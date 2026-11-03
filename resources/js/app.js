import { createIcons, icons } from 'lucide';

// Initialize Lucide icons on DOM ready and expose globally for dynamic UI updates
window.initLucideIcons = () => {
    createIcons({ icons });
};

document.addEventListener('DOMContentLoaded', () => {
    window.initLucideIcons();
});

// Global UI helper for Toast notifications
window.showToast = (message, type = 'success') => {
    const toastContainer = document.getElementById('toast-container');
    if (!toastContainer) return;

    const toast = document.createElement('div');
    const colors = {
        success: 'bg-emerald-50 text-emerald-800 border-emerald-200',
        error: 'bg-rose-50 text-rose-800 border-rose-200',
        info: 'bg-indigo-50 text-indigo-800 border-indigo-200',
        warning: 'bg-amber-50 text-amber-800 border-amber-200',
    };

    toast.className = `flex items-center gap-2 px-4 py-3 rounded-lg border text-sm font-medium shadow-sm transition-all duration-300 transform translate-y-2 opacity-0 ${colors[type] || colors.info}`;
    toast.innerHTML = `<span>${message}</span>`;
    toastContainer.appendChild(toast);

    requestAnimationFrame(() => {
        toast.classList.remove('translate-y-2', 'opacity-0');
    });

    setTimeout(() => {
        toast.classList.add('opacity-0', 'translate-y-2');
        setTimeout(() => toast.remove(), 300);
    }, 4000);
};
