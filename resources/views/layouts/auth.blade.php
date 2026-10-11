<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('layouts.partials.head')
    <title>@yield('title') · BudgetWise</title>
</head>
<body class="min-h-screen">
    <div class="mx-auto flex min-h-screen max-w-4xl items-center px-4 py-8 sm:px-6">
        <div class="grid w-full overflow-hidden rounded-2xl border-2 border-ink md:grid-cols-2">
            <aside class="flex flex-col border-b-2 border-ink px-7 py-8 md:border-b-0 md:border-r-2 md:py-10">
                <a href="{{ route('login') }}" aria-label="BudgetWise">
                    <x-wordmark size="text-[28px]" />
                </a>

                <p class="mt-8 font-serif text-[22px] leading-snug">A small notebook for where your money goes.</p>
                <p class="mt-3 text-[15px] text-ink-soft">
                    Write down what you spend, set a limit for each month, and see what's left. That's it.
                </p>

                <p class="mt-8 font-hand text-2xl text-pine md:mt-auto">— made by Ahmed</p>
            </aside>

            <section class="bg-paper-2 px-7 py-8 md:py-10">
                <h1 class="text-2xl">@yield('heading')</h1>
                @hasSection('subheading')
                    <p class="mt-1 text-sm text-ink-soft">@yield('subheading')</p>
                @endif

                <div class="mt-6">
                    @yield('content')
                </div>
            </section>
        </div>
    </div>
</body>
</html>
