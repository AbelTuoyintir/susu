import './bootstrap';

/**
 * SweetAlert2 helpers.
 *
 * Usage:
 *   window.SweetAlerts.success('Saved', 'Your changes were applied');
 *   window.SweetAlerts.error('Oops', 'Something went wrong');
 */
window.SweetAlerts = {
  success(title = 'Success', message = '') {
    if (!window.Swal || typeof window.Swal.fire !== 'function') return;
    window.Swal.fire({
      background: '#161b22',
      color: '#e6edf3',
      icon: 'success',
      iconColor: '#3fb950',
      title,
      ...(message ? { text: message } : {}),
      confirmButtonColor: '#00d4a8',
      timer: 2500,
      timerProgressBar: true,
      showConfirmButton: false,
    });
  },

  error(title = 'Error', message = '') {
    if (!window.Swal || typeof window.Swal.fire !== 'function') return;
    window.Swal.fire({
      background: '#161b22',
      color: '#e6edf3',
      icon: 'error',
      iconColor: '#f85149',
      title,
      ...(message ? { text: message } : {}),
      confirmButtonColor: '#00d4a8',
      timer: 3000,
      timerProgressBar: true,
      showConfirmButton: false,
    });
  },
};

// Read session flash values emitted by the server (via layouts).
// These are rendered into the DOM as hidden inputs.
(function bootstrapSessionToSwal() {
  const successEl = document.querySelector('[data-flash="success"]');
  const errorEl = document.querySelector('[data-flash="error"]');

  const successText = successEl?.getAttribute('data-message') || '';
  const errorText = errorEl?.getAttribute('data-message') || '';

  if (successText) {
    // Use microtask so Swal is ready if CDN loads slightly later.
    queueMicrotask(() => window.SweetAlerts?.success('Success', successText));
  }

  if (errorText) {
    queueMicrotask(() => window.SweetAlerts?.error('Error', errorText));
  }
})();

// Also support server-side session keys rendered by other layouts/components
// using a shared convention: <input data-flash="..." data-message="..." />.


