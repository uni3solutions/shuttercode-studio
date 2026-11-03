<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Package;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PackageController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();

        // Auto-assign any legacy packages where created_by is null so telecaller name is always present
        if (Package::whereNull('created_by')->exists()) {
            $defaultCreator = User::where('role', 'telecaller')->first() ?? User::whereIn('role', ['admin', 'super_admin'])->first();
            if ($defaultCreator) {
                Package::whereNull('created_by')->update(['created_by' => $defaultCreator->id]);
            }
        }

        $query = Package::with('creator')->withCount('shoots');

        // Scoping for telecallers vs admins
        if ($user?->isTelecaller()) {
            $query->where('created_by', $user->id);
        } else {
            // Telecaller filter (or scope=my)
            $telecallerId = $request->input('telecaller_id');
            $scope = $request->input('scope');

            if ($scope === 'my' || $telecallerId === 'me') {
                $query->where('created_by', auth()->id());
            } elseif (! empty($telecallerId) && $telecallerId !== 'all') {
                $query->where('created_by', (int) $telecallerId);
            }
        }

        // Search query
        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('deliverables', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhereHas('creator', function ($cq) use ($search) {
                        $cq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        // Category filter
        if ($category = $request->input('category')) {
            if ($category !== 'all') {
                $query->where('category', $category);
            }
        }

        // Status filter
        if ($status = $request->input('status')) {
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50, 100])
            ? (int) $request->input('per_page')
            : 10;

        $packages = $query->orderBy('created_at', 'desc')->orderBy('id', 'desc')->paginate($perPage)->withQueryString();

        // KPI stats and filters
        if ($user?->isTelecaller()) {
            $baseStats = Package::where('created_by', $user->id);
            $totalCount = (clone $baseStats)->count();
            $activeCount = (clone $baseStats)->where('is_active', true)->count();
            $myPackagesCount = $totalCount;
            $totalShootsBooked = (clone $baseStats)->withCount('shoots')->get()->sum('shoots_count');
            $categories = (clone $baseStats)->distinct()->orderBy('category')->pluck('category')->filter()->values()->all();
            $telecallers = User::where('id', $user->id)->get(['id', 'name', 'role', 'specialization', 'email', 'phone']);
        } else {
            $totalCount = Package::count();
            $activeCount = Package::where('is_active', true)->count();
            $myPackagesCount = Package::where('created_by', auth()->id())->count();
            $totalShootsBooked = Package::withCount('shoots')->get()->sum('shoots_count');
            $categories = Package::distinct()->orderBy('category')->pluck('category')->filter()->values()->all();
            $telecallers = User::where('role', 'telecaller')
                ->orWhereHas('createdPackages')
                ->orderBy('name')
                ->get(['id', 'name', 'role', 'specialization', 'email', 'phone']);
        }

        if (empty($categories)) {
            $categories = ['Wedding', 'Pre-Wedding', 'Maternity', 'Corporate', 'Birthday', 'Fashion'];
        }

        return view('packages.index', compact(
            'packages',
            'telecallers',
            'categories',
            'totalCount',
            'activeCount',
            'myPackagesCount',
            'totalShootsBooked'
        ));
    }

    public function create(): View
    {
        $telecallers = User::where('role', 'telecaller')
            ->orWhereIn('role', ['admin', 'super_admin'])
            ->orderBy('name')
            ->get(['id', 'name', 'role']);

        return view('packages.create', compact('telecallers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'min:0'],
            'advance_amount' => ['required', 'numeric', 'min:0'],
            'duration_hours' => ['required', 'integer', 'min:1'],
            'photographers_count' => ['required', 'integer', 'min:1'],
            'videographers_count' => ['required', 'integer', 'min:0'],
            'drone_included' => ['boolean'],
            'album_count' => ['required', 'integer', 'min:0'],
            'deliverables' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'created_by' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $validated['drone_included'] = $request->boolean('drone_included');
        $validated['is_active'] = $request->boolean('is_active', true);

        if (auth()->user()?->isAdmin() && ! empty($validated['created_by'])) {
            // Keep assigned user
        } else {
            $validated['created_by'] = auth()->id();
        }

        $package = Package::create($validated);

        ActivityLog::log('package_created', "Photography package '{$package->name}' was added by ".(auth()->user()?->name ?? 'Staff').'.', $package);

        return redirect()->route('packages.index')
            ->with('success', "Package '{$package->name}' created successfully.");
    }

    public function edit(Package $package): View
    {
        $user = auth()->user();
        if ($user?->isTelecaller() && $package->created_by !== $user->id) {
            abort(403, 'Unauthorized access to this package.');
        }

        $telecallers = User::where('role', 'telecaller')
            ->orWhereIn('role', ['admin', 'super_admin'])
            ->orderBy('name')
            ->get(['id', 'name', 'role']);

        return view('packages.edit', compact('package', 'telecallers'));
    }

    public function update(Request $request, Package $package): RedirectResponse
    {
        $user = auth()->user();
        if ($user?->isTelecaller() && $package->created_by !== $user->id) {
            abort(403, 'Unauthorized access to this package.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'min:0'],
            'advance_amount' => ['required', 'numeric', 'min:0'],
            'duration_hours' => ['required', 'integer', 'min:1'],
            'photographers_count' => ['required', 'integer', 'min:1'],
            'videographers_count' => ['required', 'integer', 'min:0'],
            'album_count' => ['required', 'integer', 'min:0'],
            'deliverables' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'created_by' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $validated['drone_included'] = $request->boolean('drone_included');
        $validated['is_active'] = $request->boolean('is_active');

        if (auth()->user()?->isAdmin() && $request->filled('created_by')) {
            $validated['created_by'] = $request->integer('created_by');
        }

        $package->update($validated);

        ActivityLog::log('package_updated', "Photography package '{$package->name}' updated.", $package);

        return redirect()->route('packages.index')
            ->with('success', "Package '{$package->name}' updated successfully.");
    }

    public function toggleStatus(Package $package): RedirectResponse
    {
        $user = auth()->user();
        if ($user?->isTelecaller() && $package->created_by !== $user->id) {
            abort(403, 'Unauthorized access to this package.');
        }

        $package->is_active = ! $package->is_active;
        $package->save();

        return back()->with('success', "Package '{$package->name}' is now ".($package->is_active ? 'Active' : 'Inactive').'.');
    }

    public function destroy(Package $package): RedirectResponse
    {
        $user = auth()->user();
        if ($user?->isTelecaller() && $package->created_by !== $user->id) {
            abort(403, 'Unauthorized access to this package.');
        }

        $name = $package->name;
        $package->delete();

        return redirect()->route('packages.index')
            ->with('success', "Package '{$name}' deleted.");
    }
}
