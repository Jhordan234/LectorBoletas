document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('register-form');
    const errorMessage = document.getElementById('error-message');
    const inputs = form.querySelectorAll('input');
    const password = document.getElementById('contraseña');
    const confirmPassword = document.getElementById('confirm_contraseña');

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

        if (password.value !== confirmPassword.value) {
            password.classList.add('border-red-500');
            confirmPassword.classList.add('border-red-500');
            hasError = true;
            errorMessage.textContent = 'Las contraseñas no coinciden.';
            errorMessage.classList.remove('hidden');
            errorMessage.classList.add('shake');
        } else if (password.value.length < 6) {
            password.classList.add('border-red-500');
            hasError = true;
            errorMessage.textContent = 'La contraseña debe tener al menos 6 caracteres.';
            errorMessage.classList.remove('hidden');
            errorMessage.classList.add('shake');
        }

        if (hasError) {
            e.preventDefault();
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