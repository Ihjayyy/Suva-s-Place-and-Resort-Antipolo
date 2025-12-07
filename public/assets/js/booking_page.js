let selectedCottage = null;
let selectedRoom = null;

// Modal Functions - Define globally before DOMContentLoaded
window.openModal = function(id) {
    const modal = document.getElementById(`modal-${id}`);
    const overlay = document.getElementById('modalOverlay');
    
    if (modal && overlay) {
        modal.classList.add('active');
        overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
};

window.closeModal = function() {
    const modals = document.querySelectorAll('.modal');
    const overlay = document.getElementById('modalOverlay');
    
    modals.forEach(modal => modal.classList.remove('active'));
    if (overlay) overlay.classList.remove('active');
    document.body.style.overflow = '';
};

document.addEventListener('DOMContentLoaded', function() {
    initializeBookingPage();
});

function initializeBookingPage() {
    // Handle payment method selection
    const paymentOptions = document.querySelectorAll('.payment-option');
    paymentOptions.forEach(option => {
        option.addEventListener('click', handlePaymentMethodChange);
    });

    // Handle file upload
    const fileInput = document.getElementById('proofOfPayment');
    if (fileInput) {
        fileInput.addEventListener('change', handleFileUpload);
    }

    // Handle form submission
    const bookingForm = document.getElementById('bookingForm');
    if (bookingForm) {
        bookingForm.addEventListener('submit', handleFormSubmit);
    }

    // Set minimum datetime for check-in
    const checkInInput = document.getElementById('checkIn');
    const checkOutInput = document.getElementById('checkOut');
    
    if (checkInInput) {
        checkInInput.addEventListener('change', function() {
            checkOutInput.min = this.value;
        });
    }

    // Close modal when clicking overlay
    const modalOverlay = document.getElementById('modalOverlay');
    if (modalOverlay) {
        modalOverlay.addEventListener('click', closeModal);
    }
}

// Service Selection - Separate for cottages and rooms
window.selectService = function(serviceId, name, option, price, type) {
    // Determine if this is a cottage or room based on service ID or name
    const isCottage = serviceId <= 7 || name.toLowerCase().includes('cottage') || name.toLowerCase().includes('silong');
    
    if (isCottage) {
        // Replace cottage selection
        selectedCottage = {
            serviceId: serviceId,
            name: name,
            option: option,
            price: price,
            type: 'cottage'
        };
        showAlert('Cottage selected successfully!', 'success');
    } else {
        // Replace room selection
        selectedRoom = {
            serviceId: serviceId,
            name: name,
            option: option,
            price: price,
            type: 'room'
        };
        showAlert('Room selected successfully!', 'success');
    }

    updateSelectedItemsDisplay();
    updateTotalAmount();
    closeModal();
};

window.removeCottage = function() {
    selectedCottage = null;
    updateSelectedItemsDisplay();
    updateTotalAmount();
    showAlert('Cottage removed', 'info');
};

window.removeRoom = function() {
    selectedRoom = null;
    updateSelectedItemsDisplay();
    updateTotalAmount();
    showAlert('Room removed', 'info');
};

function updateSelectedItemsDisplay() {
    const listElement = document.getElementById('selectedList');
    
    if (!selectedCottage && !selectedRoom) {
        listElement.innerHTML = '<p class="no-selection">No accommodation selected yet. Please select at least one cottage.</p>';
        return;
    }

    let html = '';
    
    // Display cottage if selected
    if (selectedCottage) {
        html += `
            <div class="selected-item-card cottage-card">
                <div class="selected-item-header">
                    <i class="fas fa-home"></i>
                    <strong>${selectedCottage.name}</strong>
                    <span class="item-badge">Cottage</span>
                </div>
                <div class="selected-item-body">
                    <div class="selected-item-info">
                        <span class="item-label">Option:</span>
                        <span class="item-value">${selectedCottage.option}</span>
                    </div>
                    <div class="selected-item-price">
                        <span class="price-label">Price:</span>
                        <span class="price-value">₱${selectedCottage.price.toLocaleString('en-PH', { minimumFractionDigits: 2 })}</span>
                    </div>
                </div>
                <button type="button" class="remove-selection-btn" onclick="removeCottage()">
                    <i class="fas fa-times"></i> Remove Cottage
                </button>
            </div>
        `;
    }
    
    // Display room if selected
    if (selectedRoom) {
        html += `
            <div class="selected-item-card room-card">
                <div class="selected-item-header">
                    <i class="fas fa-bed"></i>
                    <strong>${selectedRoom.name}</strong>
                    <span class="item-badge room-badge">Room</span>
                </div>
                <div class="selected-item-body">
                    <div class="selected-item-info">
                        <span class="item-label">Option:</span>
                        <span class="item-value">${selectedRoom.option}</span>
                    </div>
                    <div class="selected-item-price">
                        <span class="price-label">Price:</span>
                        <span class="price-value">₱${selectedRoom.price.toLocaleString('en-PH', { minimumFractionDigits: 2 })}</span>
                    </div>
                </div>
                <button type="button" class="remove-selection-btn" onclick="removeRoom()">
                    <i class="fas fa-times"></i> Remove Room
                </button>
            </div>
        `;
    }
    
    listElement.innerHTML = html;
}

function updateTotalAmount() {
    let total = 0;
    
    if (selectedCottage) {
        total += selectedCottage.price;
    }
    
    if (selectedRoom) {
        total += selectedRoom.price;
    }
    
    const totalElement = document.getElementById('totalAmount');
    if (totalElement) {
        totalElement.textContent = '₱' + total.toLocaleString('en-PH', { minimumFractionDigits: 2 });
    }
}

function handlePaymentMethodChange(e) {
    const option = e.currentTarget;
    const method = option.getAttribute('data-method');
    
    // Update active state
    document.querySelectorAll('.payment-option').forEach(opt => {
        opt.classList.remove('active');
    });
    option.classList.add('active');
    
    // Update hidden input
    document.getElementById('paymentMethod').value = method;
    
    // Show/hide proof upload
    const proofUpload = document.getElementById('proofUpload');
    const proofInput = document.getElementById('proofOfPayment');
    
    if (method === 'online') {
        proofUpload.style.display = 'block';
        proofInput.required = true;
    } else {
        proofUpload.style.display = 'none';
        proofInput.required = false;
    }
}

function handleFileUpload(e) {
    const file = e.target.files[0];
    const fileInfo = document.getElementById('fileInfo');
    const filePreview = document.getElementById('filePreview');
    
    if (file) {
        fileInfo.textContent = file.name;
        filePreview.style.display = 'block';
        
        // Show preview for images
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                filePreview.innerHTML = `<img src="${e.target.result}" alt="Payment proof preview" style="max-width: 300px; border-radius: 8px;">`;
            };
            reader.readAsDataURL(file);
        } else {
            filePreview.innerHTML = `<p><i class="fas fa-file-pdf"></i> ${file.name}</p>`;
        }
    } else {
        fileInfo.textContent = 'No file selected';
        filePreview.innerHTML = '';
        filePreview.style.display = 'none';
    }
}

async function handleFormSubmit(e) {
    e.preventDefault();
    
    // Validate cottage selection (required)
    if (!selectedCottage) {
        showAlert('Please select a cottage before booking', 'error');
        return;
    }
    
    const submitBtn = document.getElementById('submitBtn');
    const originalText = submitBtn.innerHTML;
    
    // Disable button and show loading
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    
    try {
        const formData = new FormData(e.target);
        
        // Create selected items array
        const selectedItems = [];
        
        if (selectedCottage) {
            selectedItems.push(selectedCottage);
        }
        
        if (selectedRoom) {
            selectedItems.push(selectedRoom);
        }
        
        // Calculate total
        const totalAmount = (selectedCottage ? selectedCottage.price : 0) + 
                           (selectedRoom ? selectedRoom.price : 0);
        
        // Add selected items to form data
        formData.append('selectedItems', JSON.stringify(selectedItems));
        formData.append('totalAmount', totalAmount);
        
        const response = await fetch('process_booking.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert(result.message, 'success');
            
            // Redirect after 2 seconds
            setTimeout(() => {
                window.location.href = result.redirect;
            }, 2000);
        } else {
            showAlert(result.message, 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('An error occurred while processing your booking. Please try again.', 'error');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
}

function showAlert(message, type) {
    // Remove existing alerts
    const existingAlerts = document.querySelectorAll('.booking-alert');
    existingAlerts.forEach(alert => alert.remove());
    
    // Create new alert
    const alert = document.createElement('div');
    alert.className = `booking-alert booking-alert-${type}`;
    
    const icon = type === 'success' ? 'check-circle' : 
                 type === 'error' ? 'exclamation-circle' : 
                 'info-circle';
    
    alert.innerHTML = `
        <div class="alert-content">
            <i class="fas fa-${icon}"></i>
            <span>${message}</span>
        </div>
        <button type="button" class="alert-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    // Insert at top of page wrapper
    const pageWrapper = document.querySelector('.page-wrapper');
    if (pageWrapper) {
        pageWrapper.insertBefore(alert, pageWrapper.firstChild);
        
        // Scroll to alert
        alert.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        alert.remove();
    }, 5000);
}