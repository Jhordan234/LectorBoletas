const video = document.getElementById('video');
const canvas = document.getElementById('canvas');
const captureButton = document.getElementById('capture');
const jsonModal = document.getElementById('jsonModal');
const editModal = document.getElementById('editModal');
const openJsonModal = document.getElementById('openJsonModal');
const closeJsonModal = document.querySelector('.json-modal-close');
const editDataButton = document.getElementById('editData');
const closeEditModal = document.querySelector('.edit-modal-close');
const confirmYes = document.getElementById('confirmYes');
const confirmNo = document.getElementById('confirmNo');
const editConfirmYes = document.getElementById('editConfirmYes');
const editConfirmNo = document.getElementById('editConfirmNo');

// Mostrar datos extraídos en el primer modal
if (extractedData) {
    document.getElementById('jsonData').textContent = JSON.stringify(extractedData, null, 2);
}

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
        fetch('sesion.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'clear_pending=true'
        });
    });
}

if (editDataButton) {
    editDataButton.addEventListener('click', () => {
        // Rellenar el formulario de edición con los datos extraídos
        if (extractedData) {
            document.getElementById('edit_tipo_comprobante').value = extractedData.tipo_comprobante || '';
            document.getElementById('edit_serie_y_numero').value = extractedData.serie_y_numero || '';
            document.getElementById('edit_fecha').value = extractedData.fecha || '';
            document.getElementById('edit_moneda').value = extractedData.moneda || '';
            document.getElementById('edit_documento_de_identidad').value = extractedData.documento_de_identidad || '';
            document.getElementById('edit_nombre_del_cliente').value = extractedData.nombre_del_cliente || '';
            document.getElementById('edit_subtotal').value = extractedData.subtotal || '0.00';
            document.getElementById('edit_igv').value = extractedData.igv || '0.00';
            document.getElementById('edit_importe_total').value = extractedData.importe_total || '0.00';
        }
        jsonModal.style.display = 'none';
        editModal.classList.remove('hidden');
        editModal.style.display = 'flex';
    });
}

if (closeEditModal) {
    closeEditModal.addEventListener('click', () => {
        editModal.style.display = 'none';
    });
}

if (confirmYes) {
    confirmYes.addEventListener('click', () => {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'sesion.php';
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'confirm_save';
        input.value = 'yes';
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    });
}

if (confirmNo) {
    confirmNo.addEventListener('click', () => {
        jsonModal.style.display = 'none';
        fetch('sesion.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'clear_pending=true'
        });
    });
}

if (editConfirmYes) {
    editConfirmYes.addEventListener('click', () => {
        // Recolectar datos editados del formulario
        const formData = {
            tipo_comprobante: document.getElementById('edit_tipo_comprobante').value,
            serie_y_numero: document.getElementById('edit_serie_y_numero').value,
            fecha: document.getElementById('edit_fecha').value,
            moneda: document.getElementById('edit_moneda').value,
            documento_de_identidad: document.getElementById('edit_documento_de_identidad').value,
            nombre_del_cliente: document.getElementById('edit_nombre_del_cliente').value,
            subtotal: document.getElementById('edit_subtotal').value,
            igv: document.getElementById('edit_igv').value,
            importe_total: document.getElementById('edit_importe_total').value
        };

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'sesion.php';
        
        const confirmInput = document.createElement('input');
        confirmInput.type = 'hidden';
        confirmInput.name = 'confirm_save';
        confirmInput.value = 'yes';
        form.appendChild(confirmInput);

        const editedDataInput = document.createElement('input');
        editedDataInput.type = 'hidden';
        editedDataInput.name = 'edited_data';
        editedDataInput.value = JSON.stringify(formData);
        form.appendChild(editedDataInput);

        document.body.appendChild(form);
        form.submit();
    });
}

if (editConfirmNo) {
    editConfirmNo.addEventListener('click', () => {
        editModal.style.display = 'none';
        jsonModal.style.display = 'flex'; // Volver al modal inicial
    });
}

window.addEventListener('click', (e) => {
    if (e.target === jsonModal) {
        jsonModal.style.display = 'none';
        fetch('sesion.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'clear_pending=true'
        });
    }
    if (e.target === editModal) {
        editModal.style.display = 'none';
        jsonModal.style.display = 'flex'; // Volver al modal inicial
    }
});