import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Source Sans 3', ...defaultTheme.fontFamily.sans],
                display: ['Fraunces', ...defaultTheme.fontFamily.serif],
            },
            colors: {
                // Neutral greys — night theme reads as dark grey (not green-black)
                ink: {
                    50: '#f5f5f5',
                    100: '#eaeaeb',
                    200: '#d4d4d6',
                    300: '#b0b0b4',
                    400: '#8a8a90',
                    500: '#6b6b72',
                    600: '#525259',
                    700: '#3f3f46',
                    800: '#2e2e33',
                    900: '#242428',
                    950: '#1c1c1f',
                },
                sun: {
                    400: '#e8b84a',
                    500: '#d4a017',
                    600: '#b8840f',
                },
            },
        },
    },

    plugins: [forms],
};
