const video = document.getElementById('video');
const canvas = document.getElementById('canvas');
const captureButton = document.getElementById('capture');
const jsonModal = document.getElementById('jsonModal');
const openJsonModal = document.getElementById('openJsonModal');
const closeJsonModal = document.querySelector('.json-modal-close');
const confirmNo = document.getElementById('confirmNo');

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

if (confirmNo) {
    confirmNo.addEventListener('click', () => {
        jsonModal.style.display = 'none';
        // Limpiar datos temporales
        fetch('sesion.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'clear_pending=true'
        });
    });
}

window.addEventListener('click', (e) => {
    if (e.target === jsonModal) {
        jsonModal.style.display = 'none';
        // Limpiar datos temporales al cerrar el modal
        fetch('sesion.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'clear_pending=true'
        });
    }
});