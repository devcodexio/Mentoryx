// Global Loader Utils
const Loader = {
    show: function(text = 'Cargando...') {
        const overlay = document.getElementById('global-loader');
        if (overlay) {
            const textEl = overlay.querySelector('.loader-text');
            if (textEl) textEl.textContent = text;
            overlay.classList.add('active');
        }
    },
    hide: function() {
        const overlay = document.getElementById('global-loader');
        if (overlay) {
            overlay.classList.remove('active');
        }
    }
};

// Configure SweetAlert2 Toast helper
const Toast = typeof Swal !== 'undefined' ? Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer)
        toast.addEventListener('mouseleave', Swal.resumeTimer)
    }
}) : null;

// Helper to show flash notifications
document.addEventListener('DOMContentLoaded', () => {
    // Look for global flash message alerts in dataset attributes
    const flashEl = document.getElementById('flash-data');
    if (flashEl && Toast) {
        const successMsg = flashEl.dataset.success;
        const errorMsg = flashEl.dataset.error;
        
        if (successMsg) {
            Toast.fire({
                icon: 'success',
                title: successMsg
            });
        }
        if (errorMsg) {
            Toast.fire({
                icon: 'error',
                title: errorMsg
            });
        }
    }
});
