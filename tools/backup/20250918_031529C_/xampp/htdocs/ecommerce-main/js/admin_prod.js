// admin_prod.js

function openModal(productId, currentStock) {
    document.getElementById('modalProductId').value = productId;
    document.getElementById('currentStock').innerText = currentStock;
    document.getElementById('stockModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('stockModal').style.display = 'none';
}

function openDeleteModal(productId) {
    document.getElementById('deleteProductId').value = productId;
    document.getElementById('deleteModal').style.display = 'block';
}

function closeDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
}


function openModal(productId, currentStock) {
document.getElementById('stockModal').style.display = 'block';
document.getElementById('modalProductId').value = productId;
document.getElementById('currentStock').textContent = currentStock;
}

function closeModal() {
document.getElementById('stockModal').style.display = 'none';
}

// Close modal when clicking outside the modal content
window.onclick = function(event) {
const modal = document.getElementById('stockModal');
if (event.target == modal) {
    modal.style.display = 'none';
}
}

// Function to handle drag-and-drop functionality
document.addEventListener('DOMContentLoaded', () => {
const dropZone = document.getElementById('dropZone');
const fileInput = document.getElementById('fileInput');
const previewImage = document.getElementById('previewImage');

// Handle clicks to open file dialog
dropZone.addEventListener('click', () => {
    fileInput.click();
});

// Handle file selection (when clicking or drag-and-dropping)
fileInput.addEventListener('change', handleFiles);
dropZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropZone.classList.add('dragover');
});

dropZone.addEventListener('dragleave', () => {
    dropZone.classList.remove('dragover');
});

dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropZone.classList.remove('dragover');
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        fileInput.files = files;
        handleFiles();
    }
});

// Function to handle file input and preview image
function handleFiles() {
    const file = fileInput.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            previewImage.src = e.target.result;
            previewImage.style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        previewImage.style.display = 'none';
    }
}
});