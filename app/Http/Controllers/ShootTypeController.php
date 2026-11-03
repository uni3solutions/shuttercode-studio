<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\ShootType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ShootTypeController extends Controller
{
    public function index(): View
    {
        $shootTypes = ShootType::with('creator')
            ->withCount(['leads', 'shoots'])
            ->orderBy('name')
            ->get();

        return view('shoot_types.index', compact('shootTypes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:shoot_types,name'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $shootType = ShootType::create([
            'name' => trim($validated['name']),
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active', true),
            'created_by' => $request->user()?->id,
        ]);

        ActivityLog::log('shoot_type_created', "Shoot type '{$shootType->name}' created.");

        return back()->with('success', "Shoot type '{$shootType->name}' created successfully!");
    }

    public function update(Request $request, ShootType $shootType): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('shoot_types', 'name')->ignore($shootType->id)],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $oldName = $shootType->name;
        $shootType->update([
            'name' => trim($validated['name']),
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        ActivityLog::log('shoot_type_updated', "Shoot type '{$oldName}' updated to '{$shootType->name}'.");

        return back()->with('success', "Shoot type '{$shootType->name}' updated successfully!");
    }

    public function toggleStatus(ShootType $shootType): RedirectResponse
    {
        $shootType->update([
            'is_active' => ! $shootType->is_active,
        ]);

        $statusText = $shootType->is_active ? 'activated' : 'deactivated';
        ActivityLog::log('shoot_type_toggled', "Shoot type '{$shootType->name}' {$statusText}.");

        return back()->with('success', "Shoot type '{$shootType->name}' {$statusText} successfully!");
    }

    public function destroy(ShootType $shootType): RedirectResponse
    {
        $name = $shootType->name;

        if ($shootType->leads()->exists() || $shootType->shoots()->exists()) {
            $shootType->update(['is_active' => false]);

            return back()->with('success', "Shoot type '{$name}' has linked leads or shoots, so it was marked as inactive instead of deleted.");
        }

        $shootType->delete();
        ActivityLog::log('shoot_type_deleted', "Shoot type '{$name}' deleted.");

        return back()->with('success', "Shoot type '{$name}' deleted successfully!");
    }
}
