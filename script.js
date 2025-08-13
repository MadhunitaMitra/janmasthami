// Simple JavaScript for ISKCON Janmashtami Donation Page
document.addEventListener('DOMContentLoaded', function() {
    initializeDonationCalculator(); 
    // addSmoothScrolling();
    addFormValidation();
    addLoadingStates();
});

let sevaAmounts = {}; // Global object to track seva amounts
let razorpayResponse = null; // Store Razorpay response globally



function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function updateTotal() {
    const totalAmountElement = document.getElementById('totalAmount');
    const modalTotalAmountElement = document.getElementById('modalTotalAmount');
    const donateButton = document.getElementById('donateButton');
    const totalAmount = Object.values(sevaAmounts).reduce((sum, amount) => sum + amount, 0);
    
    if (totalAmountElement) {
        totalAmountElement.textContent = `₹${formatNumber(totalAmount)}`;
    }
    if (modalTotalAmountElement) {
        modalTotalAmountElement.textContent = `₹${formatNumber(totalAmount)}`;
    }
    
    // Show/hide total display section
    const totalDisplaySection = document.getElementById('total-display');
    if (totalDisplaySection) {
        totalDisplaySection.style.display = totalAmount > 0 ? 'block' : 'none';
    }
    
    // Show/hide donate button based on total amount
    if (donateButton) {
        donateButton.style.display = totalAmount > 0 ? 'inline-block' : 'none';
    }
}

// This function is now defined above in the new structure

function initializeDonationCalculator() {
    // Initialize sevaAmounts with all possible sevas
    sevaAmounts = {
        kalash: 0,
        makhan: 0,
        pushpanjali: 0,
        annadaan: 0,
        gau: 0,
        vaishnav: 0,
        sadhu: 0,
        rajbhog: 0,
        any: 0  // Add any amount option
    };

    // Listen for clicks on seva buttons
    const sevaButtons = document.querySelectorAll('.btn-donate-sm[data-seva]');
    console.log('Found seva buttons:', sevaButtons.length); // Debug log
    
    if (sevaButtons.length === 0) {
        console.error('No seva buttons found! Check HTML structure.');
        return;
    }
    
    sevaButtons.forEach(function(button, index) {
        console.log(`Button ${index}:`, button.dataset.seva, button.dataset.amount); // Debug log
        
        // Add a simple click test first
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Button clicked:', button.dataset.seva, button.dataset.amount); // Debug log
            
            // Test if button click is working
            alert(`Button clicked: ${button.dataset.seva} - ₹${button.dataset.amount}`);
            
            const seva = button.dataset.seva;
            const amount = parseInt(button.dataset.amount) || 0;
            
            // Reset all buttons in the same seva category
            const sameSevaButtons = document.querySelectorAll(`[data-seva="${seva}"]`);
            sameSevaButtons.forEach(btn => btn.classList.remove('active'));
            
            // Add active class to clicked button
            button.classList.add('active');
            
            sevaAmounts[seva] = amount;
            console.log('Updated sevaAmounts:', sevaAmounts); // Debug log
            
            updateTotal();
            updateSelectedSevas();
            
            // Show modal if amount is selected
            if (amount > 0) {
                console.log('Showing modal for amount:', amount); // Debug log
                showDonorModal();
            }
        });
    });

    // Listen for any amount button
    const addAnyAmountBtn = document.getElementById('add-any-amount');
    const anyAmountInput = document.getElementById('any-amount');
    
    if (addAnyAmountBtn && anyAmountInput) {
        addAnyAmountBtn.addEventListener('click', function() {
            const amount = parseInt(anyAmountInput.value) || 0;
            if (amount > 0) {
                sevaAmounts.any = amount;
                updateTotal();
                updateSelectedSevas();
                anyAmountInput.value = '';
                showAlert('Amount added successfully!', 'success');
                showDonorModal();
            }
        });
    }
}

// Show donor modal function
function showDonorModal() {
    console.log('showDonorModal called'); // Debug log
    const totalAmount = Object.values(sevaAmounts).reduce((sum, amount) => sum + amount, 0);
    console.log('Total amount:', totalAmount); // Debug log
    
    if (totalAmount > 0) {
        const modalElement = document.getElementById('donorModal');
        console.log('Modal element:', modalElement); // Debug log
        
        if (modalElement) {
            try {
                // Try Bootstrap modal first
                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    const modal = new bootstrap.Modal(modalElement);
                    console.log('Bootstrap modal instance:', modal); // Debug log
                    modal.show();
                } else {
                    // Fallback: show modal manually
                    console.log('Bootstrap not available, showing modal manually');
                    modalElement.style.display = 'block';
                    modalElement.classList.add('show');
                    modalElement.setAttribute('aria-hidden', 'false');
                    
                    // Add backdrop
                    const backdrop = document.createElement('div');
                    backdrop.className = 'modal-backdrop fade show';
                    backdrop.id = 'manualBackdrop';
                    document.body.appendChild(backdrop);
                    
                    // Close modal when clicking backdrop
                    backdrop.addEventListener('click', function() {
                        hideDonorModal();
                    });
                }
            } catch (error) {
                console.error('Error showing modal:', error);
                // Fallback: show modal manually
                modalElement.style.display = 'block';
                modalElement.classList.add('show');
            }
        } else {
            console.error('Modal element not found!'); // Debug log
        }
    } else {
        console.log('Total amount is 0, not showing modal'); // Debug log
    }
}

// Hide donor modal function
function hideDonorModal() {
    const modalElement = document.getElementById('donorModal');
    if (modalElement) {
        modalElement.style.display = 'none';
        modalElement.classList.remove('show');
        modalElement.setAttribute('aria-hidden', 'true');
        
        // Remove manual backdrop
        const backdrop = document.getElementById('manualBackdrop');
        if (backdrop) {
            backdrop.remove();
        }
    }
}

function updateSelectedSevas() {
    const selectedSevasDiv = document.getElementById('selected-sevas');
    const sevaList = document.getElementById('seva-list');
    
    const activeSevas = [];
    for (const seva in sevaAmounts) {
        if (sevaAmounts[seva] > 0) {
            const sevaName = formatSevaName(seva);
            activeSevas.push(`${sevaName}: ₹${formatNumber(sevaAmounts[seva])}`);
        }
    }
    
    if (activeSevas.length > 0) {
        if (selectedSevasDiv) selectedSevasDiv.style.display = 'block';
        if (sevaList) sevaList.innerHTML = activeSevas.map(seva => `<div class="seva-item">${seva}</div>`).join('');
    } else {
        if (selectedSevasDiv) selectedSevasDiv.style.display = 'none';
    }
}

function formatSevaName(seva) {
    const sevaNames = {
        'kalash': 'Kalash Abhishek',
        'makhan': 'Sponsor Makhan Mishri Bhog',
        'tulsi': 'Pushpanjali',
        'annadaan': 'Anna Daan',
        'gau': 'Gau Puja',
        'vastra': 'Vastra-Alankar',
        'naivedya': 'Vishesh Naivedya',
        'any': 'General Donation'
    };
    return sevaNames[seva] || seva;
}


function submitFormDataToServer() {
    const form = document.getElementById('donationForm');
    const formData = new FormData(form);
    
    // Calculate total from sevaAmounts instead of display text
    const totalAmount = Object.values(sevaAmounts).reduce((sum, amount) => sum + amount, 0);
    

    
    // Add Razorpay details to form data if payment was successful
    console.log('Razorpay response in submitFormDataToServer:', razorpayResponse);
    if (razorpayResponse) {
        formData.append('razorpay_payment_id', razorpayResponse.razorpay_payment_id);
        formData.append('razorpay_order_id', razorpayResponse.razorpay_order_id);
        formData.append('razorpay_signature', razorpayResponse.razorpay_signature);
        console.log('Added Razorpay data to formData');
    } else {
        console.log('No razorpayResponse available');
    }
    
    formData.append('total_amount', totalAmount);
    
    for (const seva in sevaAmounts) {
        if (sevaAmounts[seva] > 0) {
            formData.append(`${seva}_amount`, sevaAmounts[seva]);
        }
    }
    

    
    fetch('process_donation.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Close the modal first
            const modal = bootstrap.Modal.getInstance(document.getElementById('donorModal'));
            if (modal) {
                modal.hide();
            }
            
            // Show success message after a short delay
            setTimeout(() => {
                showAlert('Donation successful! Thank you for your seva.', 'success');
                resetForm();
            }, 500);
        } else {
            showAlert('Payment successful but server processing failed: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Payment successful but server processing failed. Please contact support.', 'error');
    })
    .finally(() => {
        const submitButton = document.querySelector('button[type="submit"]');
        submitButton.disabled = false;
        submitButton.innerHTML = 'Donate Now';
        razorpayResponse = null; // Clear the Razorpay response
    });
}

function resetForm() {
    const form = document.getElementById('donationForm');
    form.reset();
    document.getElementById('totalAmount').textContent = '₹0';
    
    // Reset all seva buttons
    document.querySelectorAll('.btn-donate-sm[data-seva]').forEach(button => {
        button.classList.remove('active');
    });
    
    // Clear any amount input
    const anyAmountInput = document.getElementById('any-amount');
    if (anyAmountInput) {
        anyAmountInput.value = '';
    }
    
    // Reset sevaAmounts
    for (const seva in sevaAmounts) {
        sevaAmounts[seva] = 0;
    }
    
    // Hide selected sevas section and total display
    const selectedSevas = document.getElementById('selected-sevas');
    const totalDisplay = document.getElementById('total-display');
    const donateButton = document.getElementById('donateButton');
    if (selectedSevas) selectedSevas.style.display = 'none';
    if (totalDisplay) totalDisplay.style.display = 'none';
    if (donateButton) donateButton.style.display = 'none';
}

function addFormValidation() {
    const want80gCheckbox = document.getElementById('want_80g');
    const panSection = document.getElementById('pan_section');
    if (!want80gCheckbox || !panSection) return;
    
    function togglePanSection() {
        if (want80gCheckbox.checked) {
            panSection.style.display = 'block';
            document.getElementById('donor_pan').setAttribute('required', 'required');
        } else {
            panSection.style.display = 'none';
            document.getElementById('donor_pan').removeAttribute('required');
        }
    }
    want80gCheckbox.addEventListener('change', togglePanSection);
    // Initialize on page load
    togglePanSection();
}

function addLoadingStates() {
    const form = document.getElementById('donationForm');
    const submitButton = form.querySelector('button[type="submit"]');
    
    form.addEventListener('submit', function(e) {
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.innerHTML = 'Processing...';
        }
    });
}

function showAlert(message, type) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: type === 'success' ? 'success' : 'error',
            title: type === 'success' ? 'Thank you!' : 'Oops...',
            text: message,
            confirmButtonColor: '#8E2323',
        });
    } else {
        alert(message);
    }
}

// Razorpay configuration and payment logic
const razorpayOptions = {
    key: "rzp_live_gEUiOyc32j0gf5", // Replace with your actual Razorpay key
    amount: 0, // Will be set dynamically
    currency: "INR",
    name: "ISKCON New Town",
    description: "Janmashtami Donation",
    image: "Logo.png", // Use the correct filename
    payment_capture: 1, // Auto capture payment
    handler: function(response) {
        console.log('Razorpay response received:', response);
        // Store Razorpay response globally
        razorpayResponse = response;
        // Add Razorpay details to the form before AJAX
        document.getElementById('razorpay_order_id').value = response.razorpay_order_id;
        document.getElementById('razorpay_payment_id').value = response.razorpay_payment_id;
        document.getElementById('razorpay_signature').value = response.razorpay_signature;
        console.log('Form values set:', {
            order_id: response.razorpay_order_id,
            payment_id: response.razorpay_payment_id,
            signature: response.razorpay_signature
        });
        submitFormDataToServer();
    },
    prefill: {
        name: "",
        email: "",
        contact: ""
    },
    theme: {
        color: "#8E2323"
    }
};

async function processPayment() {
    const form = document.getElementById('donationForm');
    const submitButton = form.querySelector('button[type="submit"]') || document.querySelector('button[form="donationForm"]');
    const totalAmountElement = document.getElementById('modalTotalAmount') || document.getElementById('totalAmount');
    const totalAmount = totalAmountElement.textContent
        .replace('₹', '')
        .replace(/,/g, '');

    if (parseInt(totalAmount) < 101) {
        showAlert('Minimum donation amount is ₹101. Please select seva(s) with total amount of ₹101 or more.', 'error');
        submitButton.disabled = false;
        submitButton.innerHTML = 'Donate Now';
        return;
    }

    try {
        // Create Razorpay order first
        const orderResponse = await fetch('create_order.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                amount: parseInt(totalAmount)
            })
        });

        const orderData = await orderResponse.json();
        
        if (orderData.error) {
            showAlert(orderData.error, 'error');
            submitButton.disabled = false;
            submitButton.innerHTML = 'Donate Now';
            return;
        }

        razorpayOptions.amount = parseInt(totalAmount) * 100;
        razorpayOptions.order_id = orderData.id; // Add the order_id
        razorpayOptions.prefill.name = document.getElementById('donor_name').value.trim();
        razorpayOptions.prefill.email = document.getElementById('donor_email').value.trim();
        razorpayOptions.prefill.contact = document.getElementById('donor_phone').value.trim();

        const rzp = new Razorpay(razorpayOptions);
        rzp.open();

        rzp.on('payment.failed', function(response) {
            showAlert('Payment failed. Please try again.', 'error');
            submitButton.disabled = false;
            submitButton.innerHTML = 'Donate Now';
        });
    } catch (error) {
        console.error('Error creating order:', error);
        showAlert('Error creating payment order. Please try again.', 'error');
        submitButton.disabled = false;
        submitButton.innerHTML = 'Donate Now';
    }
}

// Add form submission handler
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('donationForm').addEventListener('submit', function(e) {
        e.preventDefault(); // Prevent normal form submit
        processPayment();   // Call Razorpay payment
    });
});