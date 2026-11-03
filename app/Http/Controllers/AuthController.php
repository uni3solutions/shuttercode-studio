<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\JwtService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            if (Auth::user()->isPhotographer()) {
                return redirect()->route('photographers.dashboard');
            }

            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $jwtService = app(JwtService::class);
            $token = $jwtService->generateToken(Auth::user());
            session(['jwt_token' => $token]);
            cookie()->queue('jwt_token', $token, 1440);

            if (Auth::user()->isPhotographer()) {
                return redirect()->route('photographers.dashboard')->with('success', 'Welcome, '.Auth::user()->name.'!');
            }

            return redirect()->intended(route('dashboard'))->with('success', 'Welcome back, '.Auth::user()->name.'!');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Fast 1-click quick login for multiple roles and pre-configured accounts.
     */
    public function quickLogin(Request $request): RedirectResponse
    {
        $email = $request->input('email');
        $role = $request->input('role');

        $user = null;
        if (! empty($email)) {
            $user = User::where('email', $email)->first();
        } elseif (! empty($role)) {
            $user = User::where('role', $role)->first();
        }

        if (! $user) {
            return back()->with('error', 'Selected account could not be found.');
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        $jwtService = app(JwtService::class);
        $token = $jwtService->generateToken($user);
        session(['jwt_token' => $token]);
        cookie()->queue('jwt_token', $token, 1440);

        if ($user->isPhotographer()) {
            return redirect()->route('photographers.dashboard')->with('success', "Welcome, {$user->name}! Signed in as Photographer.");
        }

        $roleLabel = ucwords(str_replace('_', ' ', $user->role));

        return redirect()->route('dashboard')->with('success', "Welcome back, {$user->name}! Signed in as {$roleLabel}.");
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        session()->forget('jwt_token');
        cookie()->queue(cookie()->forget('jwt_token'));

        return redirect()->route('login')->with('success', 'You have been signed out successfully.');
    }

    /**
     * Fast 1-click role switcher for quick testing and vibe coding.
     */
    public function switchRole(Request $request, string $role): RedirectResponse
    {
        $user = User::where('role', $role)->first();

        if ($user) {
            Auth::login($user, true);
            $request->session()->regenerate();

            $jwtService = app(JwtService::class);
            $token = $jwtService->generateToken($user);
            session(['jwt_token' => $token]);
            cookie()->queue('jwt_token', $token, 1440);

            if ($user->isPhotographer()) {
                return redirect()->route('photographers.dashboard')->with('success', "Switched view to {$user->name} (Photographer).");
            }

            $roleLabel = ucwords(str_replace('_', ' ', $user->role));

            return redirect()->route('dashboard')->with('success', "Switched active view to {$user->name} ({$roleLabel}).");
        }

        return back()->with('error', "No user found with role {$role}.");
    }
}
