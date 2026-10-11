<?php

return [

    /*
    | Days into a new month during which last month's entries can still be
    | added, edited or deleted. After that the month is closed for good.
    */
    'grace_days' => 3,

    /*
    | How long deleted entries stay in "Recently deleted" before being pruned.
    */
    'trash_days' => 30,

    /*
    | Categories every new account starts with, and the colour each one gets.
    */
    'default_categories' => [
        'Restaurants' => '#c0623b',
        'Housing' => '#2f5d50',
        'Transportation' => '#3d6b8c',
        'Shopping' => '#8a5a8c',
        'Entertainment' => '#b8892e',
        'Others' => '#6b6457',
    ],

    /*
    | Swatches offered when picking a category colour. They are chosen to sit
    | well on the paper background.
    */
    'colors' => [
        '#2f5d50', '#3d6b8c', '#8a5a8c', '#c0623b',
        '#b8892e', '#5f7a3a', '#a33f5c', '#6b6457',
    ],

    /*
    | Supported currencies: code => [symbol, symbol goes before the amount].
    */
    'currencies' => [
        'USD' => ['$', true],
        'EUR' => ['€', true],
        'GBP' => ['£', true],
        'EGP' => ['E£', true],
        'SAR' => ['SAR', false],
        'AED' => ['AED', false],
        'KWD' => ['KWD', false],
        'QAR' => ['QAR', false],
        'BHD' => ['BHD', false],
        'OMR' => ['OMR', false],
        'JOD' => ['JOD', false],
        'MAD' => ['MAD', false],
        'TND' => ['TND', false],
        'TRY' => ['₺', true],
        'INR' => ['₹', true],
        'PKR' => ['Rs', true],
        'CAD' => ['C$', true],
        'AUD' => ['A$', true],
    ],

    /*
    | Monthly <-> weekly conversion used when switching budget period.
    */
    'weeks_per_month' => 4.33,

];
