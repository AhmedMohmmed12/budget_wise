@extends('layouts.app')

@section('title', 'Profile')

@section('content')
<div class="mx-auto max-w-2xl">
    <p class="eyebrow">Profile</p>
    <h1 class="mt-1 text-3xl">{{ $user->name }}</h1>
    <p class="mt-1 text-sm text-ink-soft">Keeping this book since {{ $user->created_at?->format('F Y') }}.</p>

    @if (session('status') === 'profile-updated')
        <div class="note-pine mt-6">Saved.</div>
    @endif

    {{-- Details --}}
    <section class="mt-10">
        <h2 class="border-b border-ink pb-1.5 text-lg">Your details</h2>
        <form method="POST" action="{{ route('profile.update') }}" class="mt-5 space-y-5">
            @csrf
            @method('PATCH')

            <div>
                <label for="name" class="label">Name</label>
                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" class="field" required>
                @error('name')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="username" class="label">Username</label>
                    <input type="text" id="username" name="username" value="{{ old('username', $user->username) }}" class="field" required>
                    @error('username')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="label">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" class="field" required>
                    @error('email')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="btn-pine">Save details</button>
            </div>
        </form>
    </section>

    {{-- Preferences --}}
    <section class="mt-12" id="preferences">
        <h2 class="border-b border-ink pb-1.5 text-lg">Preferences</h2>
        @if (session('status') === 'preferences-updated')
            <div class="note-pine mt-4">Preferences saved.</div>
        @endif
        <form method="POST" action="{{ route('profile.preferences') }}" class="mt-5 space-y-5"
              x-data="{ period: '{{ old('budget_period', $user->budget_period) }}' }">
            @csrf
            @method('PATCH')

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="currency" class="label">Currency</label>
                    <select id="currency" name="currency" class="field">
                        @foreach (config('budgetwise.currencies') as $code => [$symbol])
                            <option value="{{ $code }}" @selected(old('currency', $user->currency) === $code)>{{ $code }} ({{ $symbol }})</option>
                        @endforeach
                    </select>
                    @error('currency') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="timezone" class="label">Timezone</label>
                    <select id="timezone" name="timezone" class="field">
                        @foreach (\DateTimeZone::listIdentifiers() as $zone)
                            <option value="{{ $zone }}" @selected(old('timezone', $user->timezone) === $zone)>{{ str_replace('_', ' ', $zone) }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-ink-soft">Decides when your day and month roll over.</p>
                    @error('timezone') <p class="field-error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <span class="label">Budget period</span>
                    <div class="mt-1 flex gap-5 text-[15px]">
                        <label class="flex items-center gap-2">
                            <input type="radio" name="budget_period" value="monthly" x-model="period" class="border-ink text-pine focus:ring-pine"> Monthly
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="radio" name="budget_period" value="weekly" x-model="period" class="border-ink text-pine focus:ring-pine"> Weekly
                        </label>
                    </div>
                    @error('budget_period') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div x-show="period === 'weekly'" x-cloak>
                    <label for="week_starts_on" class="label">Weeks start on</label>
                    <select id="week_starts_on" name="week_starts_on" class="field">
                        @foreach ([6 => 'Saturday', 0 => 'Sunday', 1 => 'Monday'] as $day => $label)
                            <option value="{{ $day }}" @selected((int) old('week_starts_on', $user->week_starts_on) === $day)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('week_starts_on') <p class="field-error">{{ $message }}</p> @enderror
                </div>
            </div>

            <label class="flex items-start gap-2 text-[15px]">
                <input type="checkbox" name="email_alerts" value="1" @checked(old('email_alerts', $user->email_alerts))
                       class="mt-1 rounded border-ink text-pine focus:ring-pine">
                <span>
                    Email me when I reach 80% and 100% of my budget
                    <span class="block text-xs text-ink-soft">One email per threshold per {{ $user->budget_period === 'weekly' ? 'week' : 'month' }}, for the total and each category.</span>
                </span>
            </label>

            <p class="text-xs text-ink-soft">
                Entries can be written for the current month. During the first {{ config('budgetwise.grace_days') }} days of a new month you can still fix last month; after that it's closed.
            </p>

            <div class="flex justify-end">
                <button type="submit" class="btn-pine">Save preferences</button>
            </div>
        </form>
    </section>

    {{-- Password --}}
    <section class="mt-12">
        <h2 class="border-b border-ink pb-1.5 text-lg">Change password</h2>
        <form method="POST" action="{{ route('profile.password.update') }}" class="mt-5 space-y-5">
            @csrf
            @method('PATCH')

            <div>
                <label for="current_password" class="label">Current password</label>
                <input type="password" id="current_password" name="current_password" class="field" autocomplete="current-password" required>
                @error('current_password')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="password" class="label">New password</label>
                    <input type="password" id="password" name="password" class="field" autocomplete="new-password" required>
                    <p class="mt-1 text-xs text-ink-soft">8 characters or more. A short sentence works well.</p>
                    @error('password')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="label">Same again</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="field" autocomplete="new-password" required>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="btn-pine">Update password</button>
            </div>
        </form>
    </section>

    {{-- Delete --}}
    <section class="mt-12" x-data="{ open: {{ $errors->userDeletion->isNotEmpty() ? 'true' : 'false' }} }">
        <h2 class="border-b border-rust pb-1.5 text-lg text-rust">Delete account</h2>
        <p class="mt-3 text-sm text-ink-soft">
            This removes your account and every entry in it. There's no undo.
        </p>

        <button type="button" class="btn-rust mt-4" x-show="!open" @click="open = true">Delete my account…</button>

        <form method="POST" action="{{ route('profile.destroy') }}" class="note mt-4 space-y-4" x-cloak x-show="open">
            @csrf
            @method('DELETE')

            <p>Type your password to confirm.</p>
            <div>
                <label for="delete_password" class="label !text-rust-dark">Password</label>
                <input type="password" id="delete_password" name="password" class="field !border-rust" autocomplete="current-password">
                @foreach ($errors->userDeletion->get('password') as $message)
                    <p class="field-error">{{ $message }}</p>
                @endforeach
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" class="btn-ghost" @click="open = false">Keep it</button>
                <button type="submit" class="btn !bg-rust text-paper hover:!bg-rust-dark">Delete for good</button>
            </div>
        </form>
    </section>
</div>
@endsection
