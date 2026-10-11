@props(['size' => 'text-[22px]'])

<span {{ $attributes->merge(['class' => 'relative inline-block pb-1.5']) }}>
    <span class="font-serif font-semibold tracking-tight {{ $size }}">BudgetWise</span>
    <svg class="absolute bottom-0 left-0 h-2 w-full" viewBox="0 0 118 8" preserveAspectRatio="none" aria-hidden="true">
        <path d="M2 5c20-3 45-4 70-2.5s30 1 44-.5" fill="none" stroke="#2f5d50" stroke-width="2.2" stroke-linecap="round" vector-effect="non-scaling-stroke"/>
    </svg>
</span>
