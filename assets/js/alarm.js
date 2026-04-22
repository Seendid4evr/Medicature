/**
 * Medicature Medicine Alarm System
 * Polls /api/get_due_medicines.php every 60 seconds
 * Shows browser push notification + banner if medicines are due
 */
(function () {
    let dismissed = false;

    // Request browser notification permission on load
    if ('Notification' in window && Notification.permission === 'default') {
        Notification.requestPermission();
    }

    function checkAlarms() {
        fetch('/medicure/api/get_due_medicines.php')
            .then(r => r.json())
            .then(data => {
                if (!data.alerts || data.alerts.length === 0) {
                    hideBanner();
                    return;
                }

                const overdue  = data.alerts.filter(a => a.status === 'OVERDUE');
                const dueSoon  = data.alerts.filter(a => a.status === 'DUE_SOON');
                const hasOverdue = overdue.length > 0;

                if (!dismissed) {
                    showBanner(data.alerts, hasOverdue);
                }

                // Browser/PWA push notification
                if (Notification.permission === 'granted' && data.alerts.length > 0) {
                    const first = data.alerts[0];
                    const msg = hasOverdue
                        ? `âš ï¸ Overdue: ${overdue.map(a => a.medicine_name).join(', ')}`
                        : `Time to take: ${first.medicine_name} (${first.dosage})`;

                    if ('serviceWorker' in navigator && navigator.serviceWorker.controller) {
                        navigator.serviceWorker.ready.then(sw => {
                            sw.showNotification('ðŸ’Š Medicature Reminder', {
                                body: msg,
                                icon: '/medicure/assets/icons/icon-192.png',
                                badge: '/medicure/assets/icons/icon-192.png',
                                vibrate: [200, 100, 200],
                                tag: 'medicine-alarm',
                                renotify: true,
                            });
                        });
                    } else {
                        // Fallback for non-SW contexts
                        new Notification('ðŸ’Š Medicature Reminder', { body: msg });
                    }
                }
            })
            .catch(() => {}); // Silent fail if not on dashboard
    }

    function showBanner(alerts, isOverdue) {
        let banner = document.getElementById('alarmBanner');
        if (!banner) {
            // Create banner dynamically if not in HTML
            banner = document.createElement('div');
            banner.id = 'alarmBanner';
            banner.style.cssText = `
                border-radius:12px; padding:1rem 1.5rem; margin-bottom:1.5rem;
                display:flex; align-items:center; gap:1rem; color:white;
                animation: pulse-alarm 1.5s infinite;
            `;

            const style = document.createElement('style');
            style.textContent = `
                @keyframes pulse-alarm {
                    0%,100% { box-shadow: 0 0 0 0 rgba(220,38,38,0.4); }
                    50%      { box-shadow: 0 0 0 12px rgba(220,38,38,0); }
                }
            `;
            document.head.appendChild(style);

            const container = document.querySelector('.container');
            if (container) container.prepend(banner);
        }

        banner.style.background = isOverdue
            ? 'linear-gradient(135deg,#dc2626,#b91c1c)'
            : 'linear-gradient(135deg,#1e40af,#3b82f6)';
        banner.style.animation = isOverdue ? 'pulse-alarm 1.5s infinite' : 'none';

        const icon    = isOverdue ? 'âš ï¸' : 'â°';
        const heading = isOverdue ? 'Overdue Medication!' : 'Time to Take Your Medicine!';

        const itemsHTML = alerts.map(a => {
            const tag = a.status === 'OVERDUE' ? ' <span style="opacity:0.7;">(overdue)</span>' : '';
            return `<li>â€¢ <strong>${a.medicine_name}</strong> â€” ${a.dosage} at ${a.scheduled_time}${tag}</li>`;
        }).join('');

        banner.innerHTML = `
            <div style="font-size:1.8rem;flex-shrink:0;">${icon}</div>
            <div style="flex:1;">
                <strong style="font-size:1.05rem;">${heading}</strong>
                <ul style="list-style:none;margin:0.3rem 0 0;padding:0;font-size:0.9rem;opacity:0.92;">${itemsHTML}</ul>
            </div>
            <button onclick="window.medicatureAlarmDismiss()"
                style="background:rgba(255,255,255,0.25);border:none;color:white;border-radius:8px;padding:0.4rem 0.8rem;cursor:pointer;font-size:0.9rem;">
                âœ• Dismiss
            </button>`;
        banner.style.display = 'flex';
    }

    function hideBanner() {
        const banner = document.getElementById('alarmBanner');
        if (banner) banner.style.display = 'none';
    }

    window.medicatureAlarmDismiss = function () {
        dismissed = true;
        hideBanner();
        // Reset dismiss after 10 minutes
        setTimeout(() => { dismissed = false; }, 10 * 60 * 1000);
    };

    // Check immediately + every 60 seconds
    checkAlarms();
    setInterval(checkAlarms, 60000);
})();
