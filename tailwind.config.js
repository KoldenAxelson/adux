import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ["Figtree", ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // ADUX custom color palette - to be defined in Phase 1
                // Example structure for future reference:
                // 'adux-primary': {
                //     50: '#...',
                //     100: '#...',
                //     // ... up to 950
                // },
                // 'adux-exploit': '#...', // For dark pattern indicators
                // 'adux-respect': '#...', // For ethical game indicators
            },
        },
    },

    plugins: [forms],
};
