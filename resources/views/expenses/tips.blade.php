@extends('layouts.app')

@section('title', 'Tips')

@section('content')
@php
    $sections = [
        'Every day' => [
            'Make coffee at home most days. Keep the café for when it actually means something.',
            'Bring lunch to work. Even three days a week adds up fast.',
            'Plan the week\'s meals before you shop, so less food ends up in the bin.',
            'Carry a water bottle instead of buying one each time.',
            'Take the bus or share a ride when it\'s not much slower.',
        ],
        'When you shop' => [
            'Write a list and stick to it. Impulse buys are where budgets leak.',
            'Wait 24 hours before buying anything you didn\'t plan for.',
            'Compare prices in at least two places for anything big.',
            'Buy things that keep, like rice, soap and tins, in bulk when they\'re on sale.',
            'Check second-hand first for furniture and clothes.',
        ],
        'Bills and subscriptions' => [
            'Go through your bank statement once a month and cancel what you don\'t use.',
            'Call your internet, phone and insurance providers once a year and ask for a better price.',
            'Move savings on payday, before you get a chance to spend it.',
            'Try a no-spend weekend now and then. It\'s easier than it sounds.',
        ],
        'Going out' => [
            'Look for free events, parks and community stuff before paying for plans.',
            'Use the library for books and films.',
            'Have friends over and everyone brings a dish, instead of a restaurant.',
            'Travel off-season when you can. Same place, half the price.',
        ],
        'Longer term' => [
            'Pay off the highest-interest debt first.',
            'Build a small emergency fund so a surprise bill doesn\'t go on a credit card.',
            'Learn to fix the small stuff yourself.',
        ],
    ];
    $n = 0;
@endphp

<div class="mx-auto max-w-2xl">
    <p class="eyebrow">Tips</p>
    <h1 class="mt-1 text-3xl">Small ways to spend less</h1>
    <p class="mt-2 text-ink-soft">
        None of these are new, and you don't need all of them. Pick two or three that fit your life,
        try them for a month, and see what changes on the dashboard.
    </p>

    @foreach ($sections as $title => $tips)
        <h2 class="mt-10 border-b border-ink pb-1.5 text-lg">{{ $title }}</h2>
        <ol>
            @foreach ($tips as $tip)
                @php $n++; @endphp
                <li class="flex gap-4 border-b border-dotted border-rule py-2.5 text-[15px]">
                    <span class="num w-6 shrink-0 font-mono text-sm text-ink-soft">{{ str_pad($n, 2, '0', STR_PAD_LEFT) }}</span>
                    <span>{{ $tip }}</span>
                </li>
            @endforeach
        </ol>
    @endforeach

    <div class="note-pine mt-10">
        <p class="font-serif text-base">One thing that actually works</p>
        <p class="mt-1">
            Don't just cut things. Swap them. Keep spending on what you really enjoy and replace the habits
            you only do out of routine with something cheaper you like just as much.
        </p>
    </div>
</div>
@endsection
