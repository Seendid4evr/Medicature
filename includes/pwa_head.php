<?php

?>

<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="Medicature">
<meta name="theme-color" content="#2563eb">
<meta name="description" content="Medicature - Track your medications, search our drug database, manage family prescriptions and more.">

<link rel="manifest" href="/medicure/manifest.json">

<link rel="apple-touch-icon" href="/medicure/assets/icons/icon-192.png">
<link rel="icon" type="image/png" sizes="192x192" href="/medicure/assets/icons/icon-192.png">

<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/medicure/sw.js')
                .then(reg => console.log('Medicature SW registered:', reg.scope))
                .catch(err => console.warn('SW registration failed:', err));
        });
    }
</script>
