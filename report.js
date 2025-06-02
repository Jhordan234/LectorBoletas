// Animación de desplazamiento suave para los enlaces
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        document.querySelector(this.getAttribute('href')).scrollIntoView({
            behavior: 'smooth'
        });
    });
});

// Botón para voltear todas las tarjetas
const flipAllButton = document.getElementById('flipAllCards');
const cards = document.querySelectorAll('.card');

if (flipAllButton) {
    flipAllButton.addEventListener('click', () => {
        cards.forEach(card => {
            card.classList.toggle('flipped');
        });
        if (flipAllButton.textContent === 'Ver Datos') {
            flipAllButton.textContent = 'Ocultar Datos';
        } else {
            flipAllButton.textContent = 'Ver Datos';
        }
    });
}

// Modal para mostrar imágenes
const imageModal = document.getElementById('imageModal');
const modalImage = document.getElementById('modalImage');
const closeImageModal = document.querySelector('.close-modal');
const viewImageButtons = document.querySelectorAll('.view-image');

viewImageButtons.forEach(button => {
    button.addEventListener('click', () => {
        const imageSrc = button.getAttribute('data-image');
        modalImage.src = imageSrc;
        imageModal.style.display = 'flex';
    });
});

closeImageModal.addEventListener('click', () => {
    imageModal.style.display = 'none';
});

window.addEventListener('click', (e) => {
    if (e.target === imageModal) {
        imageModal.style.display = 'none';
    }
});

// Modal para detalles
const detailModal = document.getElementById('detailModal');
const detailTitle = document.getElementById('detailTitle');
const detailValues = document.getElementById('detailValues');
const closeDetailModal = document.querySelector('.close-modal-detail');
const viewDetailButtons = document.querySelectorAll('.view-detail');

viewDetailButtons.forEach(button => {
    button.addEventListener('click', () => {
        const field = button.closest('.card').getAttribute('data-field');
        const values = datosPorCampo[field] || [];
        
        detailTitle.textContent = field === 'imagen_ruta' ? 'Imagen' : field.charAt(0).toUpperCase() + field.slice(1).replace('_', ' ');
        detailValues.innerHTML = '';
        
        if (values.length > 0) {
            values.forEach(item => {
                const p = document.createElement('p');
                if (field === 'imagen_ruta') {
                    const a = document.createElement('a');
                    a.href = '#';
                    a.className = 'text-yellow-400 hover:underline view-image-from-modal';
                    a.setAttribute('data-image', item.valor);
                    a.textContent = `Gasto #${item.id}: ${item.valor}`;
                    p.appendChild(a);
                } else {
                    p.textContent = `Gasto #${item.id}: ${item.valor}`;
                }
                detailValues.appendChild(p);
            });

            // Añadir evento a los enlaces de imágenes dentro del modal
            const viewImageFromModalLinks = detailValues.querySelectorAll('.view-image-from-modal');
            viewImageFromModalLinks.forEach(link => {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    const imageSrc = link.getAttribute('data-image');
                    modalImage.src = imageSrc;
                    imageModal.style.display = 'flex';
                    detailModal.style.display = 'none'; // Cerrar el modal de detalles
                });
            });
        } else {
            detailValues.textContent = 'No hay datos disponibles.';
        }
        
        detailModal.style.display = 'flex';
    });
});

closeDetailModal.addEventListener('click', () => {
    detailModal.style.display = 'none';
});

window.addEventListener('click', (e) => {
    if (e.target === detailModal) {
        detailModal.style.display = 'none';
    }
});

// Animación al cargar la página
window.addEventListener('load', () => {
    document.querySelectorAll('.fade-in').forEach((element, index) => {
        element.style.animationDelay = `${index * 0.2}s`;
    });

    // Generar el gráfico de barras con Chart.js
    const ctx = document.getElementById('gastosChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: chartLabels, // Ejemplo: ["Gasto #1", "Gasto #2", ...]
            datasets: [{
                label: 'Importe Total (PEN)',
                data: chartData, // Ejemplo: [630, 22, ...]
                backgroundColor: '#f4b400',
                borderColor: '#f4a400',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(255, 255, 255, 0.1)'
                    },
                    ticks: {
                        color: '#ffffff'
                    }
                },
                x: {
                    ticks: {
                        color: '#ffffff'
                    }
                }
            },
            plugins: {
                legend: {
                    labels: {
                        color: '#ffffff'
                    }
                }
            }
        }
    });
});