<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Support\BudgetPeriod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile page.
     */
    public function index(Request $request): View
    {
        return view('profile.index', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.index')->with('status', 'profile-updated');
    }

    /**
     * Update currency, timezone, budget period and alert preferences.
     */
    public function updatePreferences(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'currency' => ['required', Rule::in(array_keys(config('budgetwise.currencies')))],
            'timezone' => ['required', 'timezone:all'],
            'budget_period' => ['required', Rule::in([BudgetPeriod::MONTHLY, BudgetPeriod::WEEKLY])],
            'week_starts_on' => ['required', 'integer', Rule::in([0, 1, 6])],
            'email_alerts' => ['boolean'],
        ]);

        $user = $request->user();
        $periodChanged = $user->budget_period !== $validated['budget_period'];

        $user->update($validated + ['email_alerts' => false]);

        return $periodChanged
            ? Redirect::route('budgets.index')->with('success', 'Switched to a '.($user->budget_period === 'weekly' ? 'weekly' : 'monthly').' budget. Check the amounts below and save.')
            : Redirect::route('profile.index')->with('status', 'preferences-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'min:8', 'confirmed'],
        ]);

        $request->user()->update([
            'password' => bcrypt($request->password),
        ]);

        return Redirect::route('profile.index')->with('success', 'Password updated.');
    }
}
