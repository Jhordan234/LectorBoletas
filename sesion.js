const video = document.getElementById('video');
const canvas = document.getElementById('canvas');
const captureButton = document.getElementById('capture');

if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
    navigator.mediaDevices.getUserMedia({ video: true }).then(stream => {
        video.srcObject = stream;
        video.play();
    }).catch(err => {
        console.error("Error al acceder a la cámara: ", err);
    });
}

captureButton.addEventListener('click', () => {
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    canvas.getContext('2d').drawImage(video, 0, 0);
    const imageData = canvas.toDataURL('image/png');
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'sesion.php';
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'captured_image';
    input.value = imageData;
    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
});

// JavaScript para el modal de datos extraídos
const jsonModal = document.getElementById('jsonModal');
const openJsonModal = document.getElementById('openJsonModal');
const closeJsonModal = document.querySelector('.json-modal-close');

if (openJsonModal) {
    openJsonModal.addEventListener('click', () => {
        jsonModal.style.display = 'flex';
    });
}

if (closeJsonModal) {
    closeJsonModal.addEventListener('click', () => {
        jsonModal.style.display = 'none';
    });
}

window.addEventListener('click', (e) => {
    if (e.target === jsonModal) {
        jsonModal.style.display = 'none';
    }
});