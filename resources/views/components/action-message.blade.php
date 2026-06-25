@props(['on'])

<div
    x-data="{ shown: false, timeout: null }"
    x-init="@this.on('{{ $on }}', () => {
        clearTimeout(timeout);
        shown = true;
        timeout = setTimeout(() => { shown = false }, 2000);

        const title = 'Success';
        const message = @js($slot->isEmpty() ? __('Saved.') : (string) $slot);
        if (window.SweetAlerts && typeof window.SweetAlerts.success === 'function') {
            window.SweetAlerts.success(title, message);
        } else if (window.Swal && typeof window.Swal.fire === 'function') {
            window.Swal.fire({
                background: '#161b22',
                color: '#e6edf3',
                icon: 'success',
                iconColor: '#3fb950',
                title,
                text: message,
                confirmButtonColor: '#00d4a8',
                timer: 2500,
                timerProgressBar: true,
                showConfirmButton: false,
            });
        }
    })"
    x-show.transition.out.opacity.duration.1500ms="shown"
    x-transition:leave.opacity.duration.1500ms
    style="display: none;"
    {{ $attributes->merge(['class' => 'text-sm text-gray-600 dark:text-gray-400']) }}>
    {{ $slot->isEmpty() ? __('Saved.') : $slot }}
</div>

