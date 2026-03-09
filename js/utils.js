/**
 * Organic Ink Utilities
 * Toast, Spinner, Theme Toggle, and Auth Helpers
 */

const Utils = {
    // Toast Notification System
    showToast: (message, type = 'success') => {
        const container = document.getElementById('toast-container');
        if (!container) return;

        const icon = type === 'success' ? '✦' : (type === 'error' ? '✕' : '◈');
        const toast = document.createElement('div');
        toast.className = `toast-organic ${type}`;
        toast.innerHTML = `<span>${icon}</span> <span>${message}</span>`;
        
        container.appendChild(toast);

        setTimeout(() => {
            toast.classList.add('toast-fade-out');
            setTimeout(() => toast.remove(), 500);
        }, 4000);
    },

    // Loading Spinner Management
    setLoading: (btnId, isLoading) => {
        const btn = document.getElementById(btnId);
        if (!btn) return;

        if (isLoading) {
            if (btn.dataset.loading === "true") return;
            btn.dataset.loading = "true";
            btn.dataset.originalText = btn.innerHTML;
            
            // Capture dimensions to prevent shift
            const width = btn.offsetWidth;
            const height = btn.offsetHeight;
            btn.style.width = `${width}px`;
            btn.style.height = `${height}px`;
            
            btn.innerHTML = '<div class="spinner-organic"></div>';
            btn.disabled = true;
        } else {
            btn.dataset.loading = "false";
            btn.innerHTML = btn.dataset.originalText || btn.innerHTML;
            btn.style.width = '';
            btn.style.height = '';
            btn.disabled = false;
        }
    },

    // Theme Management
    initTheme: () => {
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
        const toggleBtn = document.getElementById('theme-toggle');
        if (toggleBtn) {
            toggleBtn.innerHTML = savedTheme === 'light' ? '🌿 Light' : '🕯 Dark';
        }
    },

    toggleTheme: () => {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';
        
        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        
        const toggleBtn = document.getElementById('theme-toggle');
        if (toggleBtn) {
            toggleBtn.innerHTML = newTheme === 'light' ? '🌿 Light' : '🕯 Dark';
        }
    },

    // Auth Helpers
    getToken: () => localStorage.getItem('auth_token'),
    setToken: (token) => localStorage.setItem('auth_token', token),
    removeToken: () => localStorage.removeItem('auth_token'),

    isLoggedIn: () => !!localStorage.getItem('auth_token'),

    logout: () => {
        Utils.removeToken();
        window.location.href = 'login.html';
    },

    // Password Strength
    checkPasswordStrength: (password) => {
        let strength = 0;
        if (password.length > 5) strength++;
        if (password.length > 10) strength++;
        if (/[A-Z]/.test(password)) strength++;
        if (/[0-9]/.test(password)) strength++;
        if (/[^A-Za-z0-9]/.test(password)) strength++;
        
        return strength; // 0-5
    }
};

// Initialize theme immediately
document.addEventListener('DOMContentLoaded', () => {
    Utils.initTheme();
    const toggleBtn = document.getElementById('theme-toggle');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', Utils.toggleTheme);
    }
});
