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
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            /** Design reference: luxury analytics (Material) — use only in admin analytics content */
            colors: {
                surface: '#f7f9fc',
                'on-surface': '#191c1e',
                'on-surface-variant': '#44474d',
                'surface-container-lowest': '#ffffff',
                'surface-container-low': '#f2f4f7',
                'surface-container-high': '#e6e8eb',
                'surface-container': '#eceef1',
                'primary-container': '#0b1f3a',
                'on-primary-container': '#7587a7',
                'on-tertiary-container': '#a87e59',
                outline: '#75777e',
                'outline-variant': '#c4c6ce',
                error: '#ba1a1a',
                /** M3 "primary" text (#000615) — named m3-ink to avoid clashing with other uses of "primary" */
                'm3-ink': '#000615',
            },
        },
    },
    safelist: [
        // Admin analytics: dynamic class strings returned from Alpine (purge-safe)
        'bg-gradient-to-r from-primary-container to-[#152a45]',
        'bg-gradient-to-r from-on-tertiary-container to-[#8a6645]',
        'bg-gradient-to-r from-primary-container/55 to-primary-container/35',
        'bg-gradient-to-r from-primary-container/75 to-primary-container/55',
    ],

    plugins: [forms],
};
