// Simple JavaScript for ISKCON Janmashtami Donation Page
document.addEventListener('DOMContentLoaded', function () {
    initializeDonationCalculator();
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

    const totalDisplaySection = document.getElementById('total-display');
    if (totalDisplaySection) {
        totalDisplaySection.style.display = totalAmount > 0 ? 'block' : 'none';
    }

    if (donateButton) {
        donateButton.style.display = totalAmount > 0 ? 'inline-block' : 'none';
    }
}

function initializeDonationCalculator() {
    sevaAmounts = {
        kalash: 0,
        makhan: 0,
        pushpanjali: 0,
        annadaan: 0,
        gau: 0,
        vaishnav: 0,
        sadhu: 0,
        rajbhog: 0,
        any: 0
    };

    const sevaButtons = document.querySelectorAll('.btn-donate-sm[data-seva]');
    if (sevaButtons.length === 0) {
        return;
    }

    sevaButtons.forEach(function (button) {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            const seva = button.dataset.seva;
            const amount = parseInt(button.dataset.amount) || 0;

            const sameSevaButtons = document.querySelectorAll(`[data-seva="${seva}"]`);
            sameSevaButtons.forEach(btn => btn.classList.remove('active'));

            button.classList.add('active');
            sevaAmounts[seva] = amount;

            updateTotal();
            updateSelectedSevas();

            if (amount > 0) {
                showDonorModal();
            }
        });
    });

    const addAnyAmountBtn = document.getElementById('add-any-amount');
    const anyAmountInput = document.getElementById('any-amount');

    if (addAnyAmountBtn && anyAmountInput) {
        addAnyAmountBtn.addEventListener('click', function () {
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

function showDonorModal() {
    const totalAmount = Object.values(sevaAmounts).reduce((sum, amount) => sum + amount, 0);
    if (totalAmount > 0) {
        const modalElement = document.getElementById('donorModal');
        if (modalElement) {
            try {
                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    const modal = new bootstrap.Modal(modalElement);
                    modal.show();
                } else {
                    modalElement.style.display = 'block';
                    modalElement.classList.add('show');
                    modalElement.setAttribute('aria-hidden', 'false');

                    const backdrop = document.createElement('div');
                    backdrop.className = 'modal-backdrop fade show';
                    backdrop.id = 'manualBackdrop';
                    document.body.appendChild(backdrop);

                    backdrop.addEventListener('click', hideDonorModal);
                }
            } catch (error) {
                modalElement.style.display = 'block';
                modalElement.classList.add('show');
            }
        }
    }
}

function hideDonorModal() {
    const modalElement = document.getElementById('donorModal');
    if (modalElement) {
        modalElement.style.display = 'none';
        modalElement.classList.remove('show');
        modalElement.setAttribute('aria-hidden', 'true');

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
    const totalAmount = Object.values(sevaAmounts).reduce((sum, amount) => sum + amount, 0);

    if (razorpayResponse) {
        formData.append('razorpay_payment_id', razorpayResponse.razorpay_payment_id);
        formData.append('razorpay_order_id', razorpayResponse.razorpay_order_id);
        formData.append('razorpay_signature', razorpayResponse.razorpay_signature);
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
                const modal = bootstrap.Modal.getInstance(document.getElementById('donorModal'));
                if (modal) modal.hide();

                setTimeout(() => {
                    showAlert('Donation successful! Thank you for your seva.', 'success');
                    resetForm();
                }, 500);
            } else {
                showAlert('Payment successful but server processing failed: ' + data.message, 'error');
            }
        })
        .catch(() => {
            showAlert('Payment successful but server processing failed. Please contact support.', 'error');
        })
        .finally(() => {
            const submitButton = document.querySelector('button[type="submit"]');
            submitButton.disabled = false;
            submitButton.innerHTML = 'Donate Now';
            razorpayResponse = null;
        });
}

function resetForm() {
    const form = document.getElementById('donationForm');
    form.reset();
    document.getElementById('totalAmount').textContent = '₹0';

    document.querySelectorAll('.btn-donate-sm[data-seva]').forEach(button => {
        button.classList.remove('active');
    });

    const anyAmountInput = document.getElementById('any-amount');
    if (anyAmountInput) anyAmountInput.value = '';

    for (const seva in sevaAmounts) {
        sevaAmounts[seva] = 0;
    }

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
    togglePanSection();
}

function addLoadingStates() {
    const form = document.getElementById('donationForm');
    const submitButton = form.querySelector('button[type="submit"]');

    form.addEventListener('submit', function () {
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

const razorpayOptions = {
    key: "rzp_live_gEUiOyc32j0gf5",
    amount: 0,
    currency: "INR",
    name: "ISKCON New Town",
    description: "Janmashtami Donation",
    image: "Logo.png",
    payment_capture: 1,
    handler: function (response) {
        razorpayResponse = response;
        document.getElementById('razorpay_order_id').value = response.razorpay_order_id;
        document.getElementById('razorpay_payment_id').value = response.razorpay_payment_id;
        document.getElementById('razorpay_signature').value = response.razorpay_signature;
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
    const totalAmount = totalAmountElement.textContent.replace('₹', '').replace(/,/g, '');

    if (parseInt(totalAmount) < 101) {
        showAlert('Minimum donation amount is ₹101. Please select seva(s) with total amount of ₹101 or more.', 'error');
        submitButton.disabled = false;
        submitButton.innerHTML = 'Donate Now';
        return;
    }

    try {
        const orderResponse = await fetch('create_order.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ amount: parseInt(totalAmount) })
        });

        const orderData = await orderResponse.json();
        if (orderData.error) {
            showAlert(orderData.error, 'error');
            submitButton.disabled = false;
            submitButton.innerHTML = 'Donate Now';
            return;
        }

        razorpayOptions.amount = parseInt(totalAmount) * 100;
        razorpayOptions.order_id = orderData.id;
        razorpayOptions.prefill.name = document.getElementById('donor_name').value.trim();
        razorpayOptions.prefill.email = document.getElementById('donor_email').value.trim();
        razorpayOptions.prefill.contact = document.getElementById('donor_phone').value.trim();

        const rzp = new Razorpay(razorpayOptions);
        rzp.open();

        rzp.on('payment.failed', function () {
            showAlert('Payment failed. Please try again.', 'error');
            submitButton.disabled = false;
            submitButton.innerHTML = 'Donate Now';
        });
    } catch {
        showAlert('Error creating payment order. Please try again.', 'error');
        submitButton.disabled = false;
        submitButton.innerHTML = 'Donate Now';
    }
}

document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('donationForm').addEventListener('submit', function (e) {
        e.preventDefault();
        processPayment();
    });
});
