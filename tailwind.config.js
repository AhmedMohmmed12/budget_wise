import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            colors: {
                paper: { DEFAULT: '#f6f1e7', 2: '#fbf8f1' },
                ink: { DEFAULT: '#22201c', soft: '#6b6457' },
                rule: { DEFAULT: '#c9bfa9', light: '#e4dccb' },
                pine: { DEFAULT: '#2f5d50', dark: '#244a40' },
                rust: { DEFAULT: '#b5452f', bg: '#fbe4dc', dark: '#7d2a1a' },
            },
            fontFamily: {
                sans: ['Nunito', ...defaultTheme.fontFamily.sans],
                serif: ['Fraunces', ...defaultTheme.fontFamily.serif],
                mono: ['"IBM Plex Mono"', ...defaultTheme.fontFamily.mono],
                hand: ['Caveat', 'cursive'],
            },
        },
    },

    plugins: [forms],
};
