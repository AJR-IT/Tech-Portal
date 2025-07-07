'use strict';

document.addEventListener('DOMContentLoaded', () => {
    const themeToggle = document.getElementById('themeToggleDropdown');
    const savedTheme = localStorage.getItem('theme');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

    function setTheme(mode) {
        document.documentElement.setAttribute('data-bs-theme', mode);
        localStorage.setItem('theme', mode);
        if (themeToggle) {
            themeToggle.checked = (mode === 'dark');
        }
    }

    // Init theme
    if (savedTheme) {
        setTheme(savedTheme);
    } else {
        setTheme(prefersDark ? 'dark' : 'light');
    }

    if (themeToggle) {
        themeToggle.addEventListener('change', () => {
            setTheme(themeToggle.checked ? 'dark' : 'light');
        });
    }
});
