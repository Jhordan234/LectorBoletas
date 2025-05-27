// Animación de desplazamiento suave para los enlaces
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        document.querySelector(this.getAttribute('href')).scrollIntoView({
            behavior: 'smooth'
        });
    });
});

// Modal para mostrar imágenes
const modal = document.getElementById('imageModal');
const modalImage = document.getElementById('modalImage');
const closeModal = document.querySelector('.close-modal');
const viewImageButtons = document.querySelectorAll('.view-image');

viewImageButtons.forEach(button => {
    button.addEventListener('click', () => {
        const imageSrc = button.getAttribute('data-image');
        modalImage.src = imageSrc;
        modal.style.display = 'flex';
    });
});

closeModal.addEventListener('click', () => {
    modal.style.display = 'none';
});

window.addEventListener('click', (e) => {
    if (e.target === modal) {
        modal.style.display = 'none';
    }
});

// Animación al cargar la página
window.addEventListener('load', () => {
    document.querySelectorAll('.fade-in').forEach((element, index) => {
        element.style.animationDelay = `${index * 0.2}s`;
    });
});