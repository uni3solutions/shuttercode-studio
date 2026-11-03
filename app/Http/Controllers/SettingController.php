<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\StudioSetting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();
        $telecallers = $user?->isAdmin()
            ? User::whereIn('role', ['telecaller', 'admin'])->orderBy('name')->get()
            : collect();

        $telecallerId = $request->query('telecaller_id');
        $targetUser = null;
        $isTelecallerScope = false;

        if ($user?->isTelecaller()) {
            $settings = StudioSetting::forUser($user);
            $isTelecallerScope = true;
            $targetUser = $user;
        } elseif ($telecallerId && $user?->isAdmin()) {
            $targetUser = User::whereIn('role', ['telecaller', 'admin'])->findOrFail($telecallerId);
            $settings = StudioSetting::forUser($targetUser);
            $isTelecallerScope = true;
        } else {
            $settings = StudioSetting::whereNull('user_id')->first() ?? StudioSetting::createDefault();
        }

        return view('settings.index', compact('settings', 'isTelecallerScope', 'telecallers', 'targetUser'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'telecaller_id' => ['nullable', 'exists:users,id'],
            'studio_name' => ['required', 'string', 'max:255'],
            'studio_tagline' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg,webp', 'max:4096'],
            'remove_logo' => ['nullable', 'boolean'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:100'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'pincode' => ['nullable', 'string', 'max:20'],
            'gstin' => ['nullable', 'string', 'max:50'],
            'default_tax_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'hsn_sac_code' => ['nullable', 'string', 'max:20'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'bank_account_name' => ['nullable', 'string', 'max:150'],
            'bank_account_number' => ['nullable', 'string', 'max:50'],
            'bank_ifsc' => ['nullable', 'string', 'max:30'],
            'bank_branch' => ['nullable', 'string', 'max:150'],
            'upi_id' => ['nullable', 'string', 'max:100'],
            'invoice_prefix' => ['required', 'string', 'max:10'],
            'invoice_terms' => ['nullable', 'string'],
            'invoice_notes' => ['nullable', 'string'],
            'authorized_signatory_name' => ['nullable', 'string', 'max:100'],
            'authorized_signatory_title' => ['nullable', 'string', 'max:100'],
        ]);

        $user = auth()->user();
        $targetUser = null;

        if ($user?->isTelecaller()) {
            $settings = StudioSetting::forUser($user);
            $targetUser = $user;
        } elseif ($request->filled('telecaller_id') && $user?->isAdmin()) {
            $targetUser = User::findOrFail($request->input('telecaller_id'));
            $settings = StudioSetting::forUser($targetUser);
        } else {
            $settings = StudioSetting::whereNull('user_id')->first() ?? StudioSetting::createDefault();
        }

        if ($request->boolean('remove_logo')) {
            if ($settings->logo_path && Storage::disk('public')->exists($settings->logo_path)) {
                Storage::disk('public')->delete($settings->logo_path);
            }
            $settings->logo_path = null;
        }

        if ($request->hasFile('logo')) {
            if ($settings->logo_path && Storage::disk('public')->exists($settings->logo_path)) {
                Storage::disk('public')->delete($settings->logo_path);
            }
            $settings->logo_path = $request->file('logo')->store('branding', 'public');
        }

        unset($validated['logo'], $validated['remove_logo'], $validated['telecaller_id']);
        $settings->fill($validated);
        $settings->save();

        $profileName = $targetUser ? "for telecaller {$targetUser->name}" : 'studio profile';
        ActivityLog::log('settings_updated', "Invoice settings updated {$profileName}.");

        $redirectUrl = route('settings.index', $targetUser && $user?->isAdmin() ? ['telecaller_id' => $targetUser->id] : []);

        return redirect($redirectUrl)
            ->with('success', 'Invoice settings and settlement configuration '.($targetUser ? "for {$targetUser->name}" : '').' updated successfully!');
    }
}
