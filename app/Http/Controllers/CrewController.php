<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class CrewController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::whereIn('role', ['telecaller', 'admin', 'super_admin'])
            ->withCount('assignedLeads');

        if ($request->filled('role')) {
            $query->where('role', $request->input('role'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('specialization', 'like', "%{$search}%");
            });
        }

        $sort = $request->input('sort', 'newest');
        match ($sort) {
            'name_desc' => $query->orderBy('name', 'desc'),
            'name_asc' => $query->orderBy('name', 'asc'),
            'leads_desc' => $query->orderBy('assigned_leads_count', 'desc'),
            'newest' => $query->orderBy('created_at', 'desc')->orderBy('id', 'desc'),
            default => $query->orderBy('created_at', 'desc')->orderBy('id', 'desc'),
        };

        $crewMembers = $query->paginate(12)->withQueryString();

        $stats = [
            'total' => User::whereIn('role', ['telecaller', 'admin', 'super_admin'])->count(),
            'telecallers' => User::where('role', 'telecaller')->count(),
            'admins' => User::whereIn('role', ['admin', 'super_admin'])->count(),
        ];

        return view('crew.index', compact('crewMembers', 'stats'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:50'],
            'role' => ['required', 'in:telecaller,admin'],
            'specialization' => ['nullable', 'string', 'max:150'],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'role' => $validated['role'],
            'specialization' => ! empty($validated['specialization']) ? $validated['specialization'] : ($validated['role'] === 'telecaller' ? 'Client Support & Telecaller' : 'Studio Operations Manager'),
            'password' => Hash::make(! empty($validated['password']) ? $validated['password'] : 'password'),
            'status' => 'active',
        ]);

        ActivityLog::log('crew_created', "Crew member {$user->name} ({$user->role}) added to studio team.", $user);

        return back()->with('success', "Crew member {$user->name} added successfully!");
    }

    public function destroy(User $crew): RedirectResponse
    {
        if ($crew->id === auth()->id()) {
            return back()->with('error', 'You cannot remove your own active account.');
        }

        $name = $crew->name;
        $crew->delete();

        ActivityLog::log('crew_deleted', "Crew member {$name} removed from studio team.");

        return back()->with('success', "Crew member {$name} removed successfully.");
    }
}
