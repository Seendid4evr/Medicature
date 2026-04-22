# Medicature - Medication Reminder System

## Overview
Medicature is a web-based application designed to help users, especially the elderly, manage their daily medications. It features a simple, accessible interface, medication scheduling, and automated reminders.

## Project Structure
```
medicature/
â”œâ”€â”€ config/
â”‚   â””â”€â”€ database.php          # Database connection settings
â”œâ”€â”€ includes/
â”‚   â”œâ”€â”€ auth.php              # Authentication logic
â”‚   â”œâ”€â”€ functions.php         # Helper functions
â”‚   â””â”€â”€ session.php           # Session management
â”œâ”€â”€ assets/
â”‚   â”œâ”€â”€ css/
â”‚   â”‚   â””â”€â”€ style.css         # Main stylesheet
â”‚   â”œâ”€â”€ js/
â”‚   â”‚   â””â”€â”€ main.js           # Frontend logic
â”‚   â””â”€â”€ images/
â”œâ”€â”€ uploads/
â”‚   â””â”€â”€ prescriptions/        # Storage for uploaded files
â”œâ”€â”€ cron/
â”‚   â””â”€â”€ reminder_check.php    # Script for automated reminders
â”œâ”€â”€ api/
â”‚   â”œâ”€â”€ check_reminders.php   # Endpoint for browser notifications
â”‚   â””â”€â”€ mark_taken.php        # Endpoint to mark medicine as taken
â”œâ”€â”€ pages/
â”‚   â”œâ”€â”€ dashboard.php         # Main user dashboard
â”‚   â”œâ”€â”€ medicines.php         # List of all medicines
â”‚   â”œâ”€â”€ add_medicine.php      # Form to add new medicine
â”‚   â”œâ”€â”€ edit_medicine.php     # Form to edit medicine
â”‚   â””â”€â”€ delete_medicine.php   # Script to delete medicine
â”œâ”€â”€ index.php                 # Entry point (redirects to login)
â”œâ”€â”€ login.php                 # User login page
â”œâ”€â”€ register.php              # User registration page
â”œâ”€â”€ logout.php                # Logout script
â””â”€â”€ database_setup.sql        # SQL script to create database tables
```

## Setup Instructions

### 1. Database Setup
1.  Open your MySQL management tool (e.g., phpMyAdmin, MySQL Workbench).
2.  Create a new database named `medicature` (or run the SQL script which creates it if it doesn't exist).
3.  Import the `database_setup.sql` file located in the root directory.

### 2. Configuration
1.  Open `medicature/config/database.php`.
2.  Update the `$username` and `$password` variables to match your local MySQL credentials.
    ```php
    private $username = 'root'; // Change to your DB username
    private $password = '';     // Change to your DB password
    ```

### 3. Running the Application
1.  Place the `medicature` folder in your web server's root directory (e.g., `htdocs` for XAMPP, `www` for WAMP).
2.  Open your browser and navigate to `http://localhost/medicature`.
3.  You will be redirected to the Login page.

### 4. Setting up Reminders (Cron Job)
To enable automated email reminders, you need to set up a cron job (Linux/Mac) or Scheduled Task (Windows) to run `medicature/cron/reminder_check.php` every minute.

**Windows Example (Task Scheduler):**
Create a `.bat` file:
```batch
"C:\path\to\php.exe" "C:\path\to\htdocs\medicature\cron\reminder_check.php"
```
Schedule this batch file to run every minute.

## Features
-   **User Authentication**: Secure login and registration.
-   **Dashboard**: View today's medication schedule at a glance.
-   **Medicine Management**: Add, edit, and delete medications with multiple daily schedules.
-   **Prescription Uploads**: Attach images or PDFs to your medications.
-   **Reminders**:
    -   **Email**: Sent via the backend script.
    -   **Browser**: In-app notifications while logged in.
-   **Responsive Design**: Works on desktop, tablets, and mobile devices.
-   **Accessibility**: High contrast, large text, and clear indicators.

## Testing
1.  **Register** a new account.
2.  **Add a Medicine** with a schedule time a few minutes from now.
3.  **Wait** for the time (or manually run the cron script) to see the email reminder.
4.  **Check the Dashboard** to see the medication listed.
5.  **Mark as Taken** to update the status.
