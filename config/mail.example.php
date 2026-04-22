<?php
// config/mail.example.php
// -------------------------------------------------------
// COPY this file to config/mail.php and fill in your
// SMTP credentials. Do NOT commit mail.php itself.
// -------------------------------------------------------
return [
    'smtp_host'   => 'smtp.gmail.com',
    'smtp_port'   => 587,
    'smtp_secure' => 'tls',
    'smtp_user'   => 'your.email@gmail.com',   // Your Gmail address
    'smtp_pass'   => 'your_app_password_here', // Gmail App Password
    'from_email'  => 'your.email@gmail.com',
    'from_name'   => 'Medicature Health Platform',
];
?>
