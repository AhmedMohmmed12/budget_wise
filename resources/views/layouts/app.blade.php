<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('layouts.partials.head')
    <title>@yield('title', 'This month') · BudgetWise</title>
    @stack('styles')
</head>
<body class="min-h-screen">
    @php
        $isActive = fn (array $link) => request()->routeIs($link['active'] ?? $link['route']);
        $links = [
            ['route' => 'dashboard', 'label' => Auth::user()?->budget_period === 'weekly' ? 'This week' : 'This month'],
            ['route' => 'entries.index', 'label' => 'Entries', 'active' => 'entries.*'],
            ['route' => 'reports.index', 'label' => 'Reports'],
            ['route' => 'goals.index', 'label' => 'Goals', 'active' => 'goals.*'],
            ['route' => 'budgets.index', 'label' => 'Budget'],
        ];
        $more = [
            ['route' => 'categories.index', 'label' => 'Categories'],
            ['route' => 'recurring.index', 'label' => 'Recurring', 'active' => 'recurring.*'],
            ['route' => 'trash.index', 'label' => 'Recently deleted'],
            ['route' => 'expense-tips', 'label' => 'Tips'],
            ['route' => 'profile.index', 'label' => 'Settings'],
        ];
    @endphp

    <header class="border-b-2 border-ink">
        <div class="mx-auto flex max-w-5xl items-center justify-between px-4 py-3.5 sm:px-6">
            <a href="{{ route('dashboard') }}" aria-label="BudgetWise home">
                <x-wordmark />
            </a>

            <nav class="hidden items-center gap-6 text-sm md:flex">
                @foreach ($links as $link)
                    <a href="{{ route($link['route']) }}"
                       class="{{ $isActive($link) ? 'font-bold text-ink' : 'text-ink-soft hover:text-ink' }}">
                        {{ $link['label'] }}
                    </a>
                @endforeach

                <div class="relative" x-data="{ open: false }" @click.outside="open = false" @keydown.escape="open = false">
                    <button type="button" @click="open = !open" class="flex items-center gap-1 text-ink-soft hover:text-ink" :aria-expanded="open">
                        {{ Auth::user()->name ?? 'You' }}
                        <svg class="h-3 w-3" viewBox="0 0 12 12" aria-hidden="true"><path d="M3 4.5l3 3 3-3" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    </button>
                    <div x-cloak x-show="open" x-transition.opacity
                         class="absolute right-0 z-20 mt-2 w-48 rounded-xl border-[1.5px] border-ink bg-paper-2 py-1 text-sm">
                        @foreach ($more as $link)
                            <a href="{{ route($link['route']) }}" class="block px-4 py-2 hover:bg-rule-light {{ $isActive($link) ? 'font-bold' : '' }}">{{ $link['label'] }}</a>
                        @endforeach
                        <form method="POST" action="{{ route('logout') }}" class="border-t border-rule-light">
                            @csrf
                            <button type="submit" class="block w-full px-4 py-2 text-left hover:bg-rule-light">Log out</button>
                        </form>
                    </div>
                </div>
            </nav>
        </div>
    </header>

    <main class="mx-auto max-w-5xl px-4 pb-28 pt-6 sm:px-6 md:pb-12">
        @if (session('success'))
            <div x-data="{ show: true }" x-show="show" class="note-pine mb-6 flex items-start justify-between gap-4">
                <span>{{ session('success') }}</span>
                <button type="button" @click="show = false" class="text-lg leading-none" aria-label="Dismiss">&times;</button>
            </div>
        @endif

        @if (session('error'))
            <div x-data="{ show: true }" x-show="show" class="note mb-6 flex items-start justify-between gap-4">
                <span>{{ session('error') }}</span>
                <button type="button" @click="show = false" class="text-lg leading-none" aria-label="Dismiss">&times;</button>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="mx-auto hidden max-w-5xl px-6 pb-8 md:block">
        <p class="border-t border-rule pt-4 text-xs text-ink-soft">BudgetWise · kept by hand, one entry at a time.</p>
    </footer>

    {{-- Mobile bottom bar --}}
    <nav class="fixed inset-x-0 bottom-0 z-30 md:hidden" x-data="{ more: false }" @keydown.escape="more = false">
        <div x-cloak x-show="more" @click.outside="more = false" x-transition.opacity
             class="mx-3 mb-2 rounded-2xl border-[1.5px] border-ink bg-paper-2 py-1 text-[15px]">
            <a href="{{ route('budgets.index') }}" class="block px-5 py-2.5">Budget</a>
            @foreach ($more as $link)
                <a href="{{ route($link['route']) }}" class="block px-5 py-2.5 {{ $isActive($link) ? 'font-bold' : '' }}">{{ $link['label'] }}</a>
            @endforeach
            <form method="POST" action="{{ route('logout') }}" class="border-t border-rule-light">
                @csrf
                <button type="submit" class="block w-full px-5 py-2.5 text-left text-ink-soft">Log out</button>
            </form>
        </div>
        <div class="grid grid-cols-5 border-t-2 border-ink bg-paper text-center text-[13px]">
            @foreach (array_slice($links, 0, 4) as $link)
                <a href="{{ route($link['route']) }}"
                   class="py-3.5 {{ $isActive($link) ? 'font-bold text-ink underline decoration-pine decoration-2 underline-offset-4' : 'text-ink-soft' }}">
                    {{ $link === $links[0] ? (Auth::user()?->budget_period === 'weekly' ? 'Week' : 'Month') : $link['label'] }}
                </a>
            @endforeach
            <button type="button" @click="more = !more" :aria-expanded="more" class="py-3.5 text-ink-soft">More</button>
        </div>
    </nav>

    @stack('scripts')
</body>
</html>
