/**
 * Medicature Medicine Alarm System
 * Polls /api/get_due_medicines.php every 60 seconds.
 * Shows an in-page banner + browser push notification when medicines are due.
 */
(function () {
    'use strict';

    let dismissed     = false;
    let lastNotifTime = 0;
    const NOTIF_GAP_MS = 60000;

    if ('Notification' in window && Notification.permission === 'default') {
        Notification.requestPermission();
    }

    (function injectCSS() {
        if (document.getElementById('alarm-pulse-style')) return;
        const style = document.createElement('style');
        style.id = 'alarm-pulse-style';
        style.textContent = `
            @keyframes pulse-alarm {
                0%,100% { box-shadow: 0 0 0 0   rgba(220,38,38,0.45); }
                50%      { box-shadow: 0 0 0 14px rgba(220,38,38,0);    }
            }
        `;
        document.head.appendChild(style);
    })();

    function checkAlarms() {
        fetch('/medicure/api/get_due_medicines.php')
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.alerts || data.alerts.length === 0) {
                    hideBanner();
                    return;
                }

                const overdue    = data.alerts.filter(function (a) { return a.status === 'OVERDUE'; });
                const hasOverdue = overdue.length > 0;

                if (!dismissed) {
                    showBanner(data.alerts, hasOverdue);
                }

                const now = Date.now();
                if (Notification.permission === 'granted' && (now - lastNotifTime) >= NOTIF_GAP_MS) {
                    lastNotifTime = now;

                    const first = data.alerts[0];
                    const msg   = hasOverdue
                        ? 'Overdue: ' + overdue.map(function (a) { return a.medicine_name; }).join(', ')
                        : 'Time to take: ' + first.medicine_name + ' (' + first.dosage + ')';

                    const title   = '\uD83D\uDC8A Medicature Reminder';
                    const options = {
                        body:     msg,
                        icon:     '/medicure/assets/icons/icon-192.png',
                        badge:    '/medicure/assets/icons/icon-192.png',
                        vibrate:  [200, 100, 200],
                        tag:      'medicine-alarm',
                        renotify: true,
                    };

                    if ('serviceWorker' in navigator && navigator.serviceWorker.controller) {
                        navigator.serviceWorker.ready.then(function (sw) {
                            sw.showNotification(title, options);
                        });
                    } else {
                        new Notification(title, options);
                    }
                }
            })
            .catch(function () {});
    }

    function showBanner(alerts, isOverdue) {
        let banner = document.getElementById('alarmBanner');

        if (!banner) {
            banner = document.createElement('div');
            banner.id = 'alarmBanner';
            banner.style.cssText = [
                'border-radius:12px',
                'padding:1rem 1.5rem',
                'margin-bottom:1.5rem',
                'display:flex',
                'align-items:flex-start',
                'gap:1rem',
                'color:white',
            ].join(';');

            const container = document.querySelector('.container');
            if (container) {
                container.prepend(banner);
            } else {
                document.body.prepend(banner);
            }
        }

        banner.style.background = isOverdue
            ? 'linear-gradient(135deg,#dc2626,#b91c1c)'
            : 'linear-gradient(135deg,#1e40af,#3b82f6)';
        banner.style.animation = isOverdue ? 'pulse-alarm 1.5s infinite' : 'none';

        const icon    = isOverdue ? '\u26A0\uFE0F' : '\u23F0';
        const heading = isOverdue ? 'Overdue Medication!' : 'Time to Take Your Medicine!';

        const itemsHTML = alerts.map(function (a) {
            const overdueTxt = a.status === 'OVERDUE'
                ? ' <span style="opacity:0.75;">(overdue)</span>'
                : '';
            return '<li>\u2022 <strong>' + a.medicine_name + '</strong> &ndash; '
                + a.dosage + ' at ' + formatAlarmTime(a.scheduled_time) + overdueTxt + '</li>';
        }).join('');

        banner.innerHTML =
            '<div style="font-size:1.8rem;flex-shrink:0;margin-top:0.1rem;">' + icon + '</div>' +
            '<div style="flex:1;">' +
                '<strong style="font-size:1.05rem;">' + heading + '</strong>' +
                '<ul style="list-style:none;margin:0.35rem 0 0;padding:0;font-size:0.9rem;opacity:0.93;">' +
                    itemsHTML +
                '</ul>' +
            '</div>' +
            '<button onclick="window.medicatureAlarmDismiss()" ' +
                'style="background:rgba(255,255,255,0.25);border:none;color:white;border-radius:8px;' +
                'padding:0.4rem 0.9rem;cursor:pointer;font-size:0.9rem;flex-shrink:0;">' +
                '\u2715 Dismiss' +
            '</button>';

        banner.style.display = 'flex';
    }

    function hideBanner() {
        const banner = document.getElementById('alarmBanner');
        if (banner) banner.style.display = 'none';
    }

    function formatAlarmTime(timeStr) {
        if (!timeStr) return '';
        try {
            const [h, m] = timeStr.split(':').map(Number);
            const ampm   = h >= 12 ? 'PM' : 'AM';
            const hour12 = ((h % 12) || 12);
            return hour12 + ':' + String(m).padStart(2, '0') + ' ' + ampm;
        } catch (e) {
            return timeStr;
        }
    }

    window.medicatureAlarmDismiss = function () {
        dismissed = true;
        hideBanner();
        setTimeout(function () { dismissed = false; }, 10 * 60 * 1000);
    };

    checkAlarms();
    setInterval(checkAlarms, 60 * 1000);
})();
