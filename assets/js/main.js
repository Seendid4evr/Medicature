// assets/js/main.js - Medicature Main JavaScript

document.addEventListener('DOMContentLoaded', function () {
    initMarkAsTaken();
    initFormValidation();
    initNotifications();
    checkReminders();
});

function initMarkAsTaken() {
    const markTakenButtons = document.querySelectorAll('.mark-taken');

    markTakenButtons.forEach(button => {
        button.addEventListener('click', async function () {
            const medicineId = this.dataset.medicineId;
            const scheduleId = this.dataset.scheduleId;

            if (!medicineId || !scheduleId) {
                showAlert('Error: Missing data', 'error');
                return;
            }

            this.disabled = true;
            const originalText = this.textContent;
            this.textContent = 'Marking...';

            try {
                const response = await fetch('../api/mark_taken.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        medicine_id: medicineId,
                        schedule_id: scheduleId,
                        taken_at: new Date().toISOString()
                    })
                });

                const result = await response.json();

                if (result.success) {
                    const scheduleItem = this.closest('.schedule-item');
                    scheduleItem.classList.add('taken');

                    const actionDiv = this.closest('.schedule-action');
                    actionDiv.innerHTML = `
                        <span class="badge badge-success">✓ Taken</span>
                        <small>at ${formatTime(new Date())}</small>
                    `;

                    showAlert('Marked as taken!', 'success');
                    updateStats();
                } else {
                    showAlert(result.message || 'Failed to mark as taken', 'error');
                    this.disabled = false;
                    this.textContent = originalText;
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('Network error. Please try again.', 'error');
                this.disabled = false;
                this.textContent = originalText;
            }
        });
    });
}

function initFormValidation() {
    const forms = document.querySelectorAll('form[data-validate]');

    forms.forEach(form => {
        form.addEventListener('submit', function (e) {
            if (!validateForm(this)) {
                e.preventDefault();
                return false;
            }
        });
    });
}

function validateForm(form) {
    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');

    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            showFieldError(field, 'This field is required');
            isValid = false;
        } else {
            clearFieldError(field);
        }
    });

    const emailFields = form.querySelectorAll('input[type="email"]');
    emailFields.forEach(field => {
        if (field.value && !isValidEmail(field.value)) {
            showFieldError(field, 'Please enter a valid email address');
            isValid = false;
        }
    });

    const password        = form.querySelector('input[name="password"]');
    const confirmPassword = form.querySelector('input[name="confirm_password"]');

    if (password && confirmPassword && password.value !== confirmPassword.value) {
        showFieldError(confirmPassword, 'Passwords do not match');
        isValid = false;
    }

    return isValid;
}

function showFieldError(field, message) {
    clearFieldError(field);
    field.classList.add('error');
    const errorDiv = document.createElement('div');
    errorDiv.className = 'field-error';
    errorDiv.textContent = message;
    errorDiv.style.color = 'var(--error-color)';
    errorDiv.style.fontSize = '0.875rem';
    errorDiv.style.marginTop = '0.25rem';
    field.parentNode.appendChild(errorDiv);
}

function clearFieldError(field) {
    field.classList.remove('error');
    const errorDiv = field.parentNode.querySelector('.field-error');
    if (errorDiv) errorDiv.remove();
}

function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function initNotifications() {
    if ('Notification' in window && Notification.permission === 'default') {
        const notifBanner = document.createElement('div');
        notifBanner.className = 'notification-banner';
        notifBanner.innerHTML = `
            <p>Enable notifications to receive medication reminders</p>
            <button class="btn btn-primary btn-sm" id="enable-notifications">Enable</button>
            <button class="btn btn-secondary btn-sm" id="dismiss-notifications">Later</button>
        `;
        notifBanner.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: var(--card-bg);
            padding: 1rem 1.5rem;
            border-radius: var(--radius);
            box-shadow: var(--shadow-lg);
            z-index: 1000;
            max-width: 350px;
        `;

        document.body.appendChild(notifBanner);

        document.getElementById('enable-notifications').addEventListener('click', async () => {
            const permission = await Notification.requestPermission();
            if (permission === 'granted') {
                showAlert('Notifications enabled!', 'success');
                notifBanner.remove();
            }
        });

        document.getElementById('dismiss-notifications').addEventListener('click', () => {
            notifBanner.remove();
        });
    }
}

function showBrowserNotification(title, options) {
    if ('Notification' in window && Notification.permission === 'granted') {
        const notification = new Notification(title, {
            icon: '../assets/images/pill-icon.png',
            badge: '../assets/images/pill-icon.png',
            ...options
        });

        const audio = new Audio('../assets/sound/alarm.mp3');
        audio.play().catch(error => {
            console.log('Audio play failed (interaction required first):', error);
        });

        notification.onclick = function () {
            window.focus();
            notification.close();
        };
    }
}

function checkReminders() {
    setInterval(async () => {
        try {
            const response = await fetch('/medicure/api/check_reminders.php');
            const result = await response.json();

            if (result.success && result.reminders && result.reminders.length > 0) {
                result.reminders.forEach(reminder => {
                    showBrowserNotification('Medication Reminder', {
                        body: `Time to take ${reminder.medicine_name} - ${reminder.dosage}`,
                        tag: `reminder-${reminder.id}`,
                        requireInteraction: true
                    });
                });
            }
        } catch (error) {
            console.error('Error checking reminders:', error);
        }
    }, 5000);
}

function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.textContent = message;
    alertDiv.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1000;
        min-width: 300px;
        animation: slideIn 0.3s ease;
    `;

    document.body.appendChild(alertDiv);

    setTimeout(() => {
        alertDiv.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => alertDiv.remove(), 300);
    }, 3000);
}

function formatTime(date) {
    return new Date(date).toLocaleTimeString('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    });
}

function formatDate(date) {
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
    });
}

function updateStats() {
    const takenCount = document.querySelectorAll('.schedule-item.taken').length;
    const statCard   = document.querySelector('.stat-card:nth-child(3) h3');
    if (statCard) statCard.textContent = takenCount;
}

function confirmDelete(medicineId, medicineName) {
    if (confirm(`Are you sure you want to delete "${medicineName}"?`)) {
        window.location.href = `delete_medicine.php?id=${medicineId}`;
    }
}

function addTimeInput() {
    const container = document.getElementById('times-container');
    if (!container) return;

    const timeCount = container.querySelectorAll('.time-input').length;
    const timeDiv   = document.createElement('div');
    timeDiv.className = 'form-group time-input';
    timeDiv.innerHTML = `
        <label>Time ${timeCount + 1}</label>
        <div style="display: flex; gap: 0.5rem;">
            <input type="time" name="times[]" required>
            <button type="button" class="btn btn-danger btn-sm" onclick="this.closest('.time-input').remove()">Remove</button>
        </div>
    `;

    container.appendChild(timeDiv);
}

function previewFile(input) {
    const preview = document.getElementById('file-preview');
    if (!preview) return;

    const file = input.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
            if (file.type.startsWith('image/')) {
                preview.innerHTML = `<img src="${e.target.result}" alt="Preview" style="max-width: 200px; border-radius: var(--radius-sm);">`;
            } else {
                preview.innerHTML = `<p>📄 ${file.name}</p>`;
            }
        };
        reader.readAsDataURL(file);
    }
}

const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to   { transform: translateX(0);   opacity: 1; }
    }
    @keyframes slideOut {
        from { transform: translateX(0);   opacity: 1; }
        to   { transform: translateX(100%); opacity: 0; }
    }
`;
document.head.appendChild(style);

window.confirmDelete = confirmDelete;
window.addTimeInput  = addTimeInput;
window.previewFile   = previewFile;
