# Medicature Project Report

## 1. Project Overview
**Medicature** is a web-based medication management system designed to help patients and caregivers track medication schedules. It provides a user-friendly dashboard to manage prescriptions, a robust notification system to remind users when to take their medicine, and profile management features.

## 2. Key Features
*   **User Management**: Secure registration and login authentication.
*   **Profile Management**: Update personal details, change passwords, and manage notification settings.
*   **Medication Tracking**: Add, edit, and view medicines with specific dosages and schedules.
*   **Smart Notifications**: Real-time browser notifications with audible alarms to remind users of due medications.
*   **Dynamic Dashboard**: Visual overview of upcoming medications and adherence status.

## 3. System Diagrams

### 3.1. Use Case Diagram
This diagram illustrates the primary interactions between the User and the Medicature system.

```mermaid
usecaseDiagram
    actor User
    actor "Database System" as DB

    usecase "Register/Login" as UC1
    usecase "Manage Medicines" as UC2
    usecase "View Dashboard" as UC3
    usecase "Mark as Taken" as UC4
    usecase "Upload Prescription" as UC5

    User -- UC1
    User -- UC2
    User -- UC3
    User -- UC4
    
    UC1 -- DB
    UC2 -- DB
    UC3 -- DB
    UC4 -- DB

    UC2 ..> UC5 : <<include>>
    UC4 ..> UC3 : <<extend>>
```

### 3.2. Activity Diagram (Notification Flow)
The following activity diagram details the process of how the system triggers text and audio reminders for the user.

```mermaid
flowchart TD
    Start((Start)) --> HasAccount{Has Account?}
    HasAccount -- No --> Register[Register]
    Register --> Login
    HasAccount -- Yes --> Login[Login]
    
    Login -- Failed login --> Login
    Login --> CheckSchedule[Check Schedule]

    subgraph "Dashboard"
        CheckSchedule --> ViewDetails[View Medicine Details]
        ViewDetails -- User Action --> MarkTaken[Mark As Taken]
        MarkTaken -- System Process --> UpdateStatus[Update Status]
        UpdateStatus -- Refresh View --> CheckSchedule
    end

    subgraph "Manage Medicine"
        ListMedicines[List Medicines] --> AddMedicine[Add Medicine]
        AddMedicine --> SaveDetails[Save Details]
        SaveDetails --> ListMedicines
        ListMedicines --> ListMedicines
    end

    UpdateStatus -- Click My Medicine --> ListMedicines
    SaveDetails -- Click Dashboard --> CheckSchedule
```

### 3.3. Database Diagram (ERD)
The database schema consists of users, medicines, schedules, and reminder logs.

```mermaid
erDiagram
    Users {
        int ID
        string Name
        string Email
        string Password_hash
    }

    Medicines {
        int Id
        int User_id
        string name
        string dosage
        text notes
        date start_date
        date end_date
        string prescription_file
        boolean active
        timestamp created_at
        timestamp updated_at
    }

    Schedules {
        int Id
        int medicine_id
        time time_of_day
        timestamp created_at
    }

    Reminders {
        int id
        int user_id
        int medicine_id
        int schedule_id
        datetime reminder_datetime
        enum status
        datetime taken_at
        timestamp created_at
    }

    Users ||--o{ Medicines : "1:N"
    Users ||--o{ Reminders : "1:N"
    Medicines ||--o{ Schedules : "1:N"
    Medicines ||--o{ Reminders : "1:N"
    Schedules ||--o{ Reminders : "1:N"
```

## 4. User Interface Gallery

### Login Page
The entry point for the application, ensuring secure access.
![Login Page](images/medicature_login_page_1766644310345.png)

### Profile Management
Users can update their personal information and securely change their passwords.
![Password Change UI](images/profile_page_password_form_1766645186343.png)

### Notification Settings
A simplified control panel to check the status of browser notifications and multiple confirmations of activity.
![Notification Settings](images/profile_notification_status_1766647690943.png)
