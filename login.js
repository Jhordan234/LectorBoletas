document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('login-form');
    const errorMessage = document.getElementById('error-message');
    const inputs = form.querySelectorAll('input');

    // Show error if it exists from PHP
    const phpError = errorMessage.getAttribute('data-error');
    if (phpError) {
        errorMessage.textContent = phpError;
        errorMessage.classList.remove('hidden');
        errorMessage.classList.add('shake');
    }

    // Client-side validation
    form.addEventListener('submit', (e) => {
        let hasError = false;
        inputs.forEach(input => {
            if (!input.value.trim()) {
                input.classList.add('border-red-500');
                hasError = true;
            } else {
                input.classList.remove('border-red-500');
                input.classList.add('border-green-500');
            }
        });

        if (hasError) {
            e.preventDefault();
            errorMessage.textContent = 'Nombre y contraseña son obligatorios.';
            errorMessage.classList.remove('hidden');
            errorMessage.classList.add('shake');
        }
    });

    // Input focus animation
    inputs.forEach(input => {
        input.addEventListener('focus', () => {
            input.parentElement.querySelector('label').classList.add('text-yellow-400', 'transform', '-translate-y-1', 'scale-95');
        });
        input.addEventListener('blur', () => {
            if (!input.value.trim()) {
                input.parentElement.querySelector('label').classList.remove('text-yellow-400', 'transform', '-translate-y-1', 'scale-95');
            }
        });
    });
});