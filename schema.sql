-- ================================================================
-- Medicature — Full Database Schema
-- Run this file once to set up the entire database from scratch.
-- ================================================================

CREATE DATABASE IF NOT EXISTS medicure
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE medicure;

-- ----------------------------------------------------------------
-- 1. USERS
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(100)  NOT NULL,
    email         VARCHAR(100)  UNIQUE NOT NULL,
    password_hash VARCHAR(255)  NOT NULL,
    raw_password  VARCHAR(255)  DEFAULT NULL,   -- stored for admin demo view
    phone         VARCHAR(20)   DEFAULT NULL,
    is_admin      TINYINT(1)    DEFAULT 0,
    role          ENUM('user','caregiver') DEFAULT 'user',
    created_at    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------
-- 2. PASSWORD RESET TOKENS
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS password_reset_tokens (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT          NOT NULL,
    token      VARCHAR(64)  NOT NULL,
    expires_at DATETIME     NOT NULL,
    used       TINYINT(1)   DEFAULT 0,
    created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_token (token),
    INDEX idx_token (token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------
-- 3. DEPENDENTS (family members)
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS dependents (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    user_id      INT          NOT NULL,
    name         VARCHAR(100) NOT NULL,
    relationship VARCHAR(50)  NOT NULL,
    created_at   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------
-- 4. BD GENERICS (21,000+ drug reference — generic compounds)
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS bd_generics (
    id                                  INT PRIMARY KEY,
    name                                VARCHAR(255) NOT NULL,
    drug_class                          VARCHAR(255),
    indication                          TEXT,
    indication_description              TEXT,
    therapeutic_class_description       TEXT,
    pharmacology_description            TEXT,
    dosage_description                  TEXT,
    administration_description          TEXT,
    interaction_description             TEXT,
    contraindications_description       TEXT,
    side_effects_description            TEXT,
    pregnancy_and_lactation_description TEXT,
    precautions_description             TEXT,
    pediatric_usage_description         TEXT,
    overdose_effects_description        TEXT,
    duration_of_treatment_description   TEXT,
    reconstitution_description          TEXT,
    storage_conditions_description      TEXT,
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------
-- 5. BD MEDICINES (21,000+ drug reference — brand products)
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS bd_medicines (
    id                INT PRIMARY KEY,
    brand_name        VARCHAR(255) NOT NULL,
    type              VARCHAR(100),
    dosage_form       VARCHAR(255),
    generic           VARCHAR(255),
    strength          VARCHAR(255),
    manufacturer      VARCHAR(255),
    package_container TEXT,
    package_size      TEXT,
    INDEX idx_brand_name (brand_name),
    INDEX idx_generic    (generic)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------
-- 6. MEDICINES (patient's personal prescription list)
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS medicines (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    user_id           INT          NOT NULL,
    dependent_id      INT          NULL DEFAULT NULL,
    bd_medicine_id    INT          NULL DEFAULT NULL,
    name              VARCHAR(200) NOT NULL,
    dosage            VARCHAR(100) NOT NULL,
    notes             TEXT,
    start_date        DATE         NOT NULL,
    end_date          DATE,
    prescription_file VARCHAR(255),
    active            BOOLEAN      DEFAULT TRUE,
    created_at        TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at        TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)        REFERENCES users(id)       ON DELETE CASCADE,
    FOREIGN KEY (dependent_id)   REFERENCES dependents(id)  ON DELETE SET NULL,
    FOREIGN KEY (bd_medicine_id) REFERENCES bd_medicines(id) ON DELETE SET NULL,
    INDEX idx_user_active (user_id, active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------
-- 7. SCHEDULES (alarm times per medicine)
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS schedules (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    medicine_id INT  NOT NULL,
    time_of_day TIME NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (medicine_id) REFERENCES medicines(id) ON DELETE CASCADE,
    INDEX idx_medicine_time (medicine_id, time_of_day)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------
-- 8. REMINDERS (dose tracking log)
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS reminders (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    user_id           INT      NOT NULL,
    medicine_id       INT      NOT NULL,
    schedule_id       INT      NOT NULL,
    reminder_datetime DATETIME NOT NULL,
    status            ENUM('pending','sent','taken','missed','snoozed') DEFAULT 'pending',
    taken_at          DATETIME,
    created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)     REFERENCES users(id)     ON DELETE CASCADE,
    FOREIGN KEY (medicine_id) REFERENCES medicines(id) ON DELETE CASCADE,
    FOREIGN KEY (schedule_id) REFERENCES schedules(id) ON DELETE CASCADE,
    INDEX idx_user_datetime (user_id, reminder_datetime),
    INDEX idx_status        (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------
-- 9. ORDERS (pharmacy orders)
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS orders (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    user_id          INT          NOT NULL,
    status           ENUM('Pending','Processing','Shipped','Delivered','Cancelled') DEFAULT 'Pending',
    total_amount     DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    shipping_address TEXT          NOT NULL,
    created_at       TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------
-- 10. ORDER ITEMS
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS order_items (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    order_id      INT          NOT NULL,
    medicine_name VARCHAR(255) NOT NULL,
    quantity      INT          NOT NULL DEFAULT 1,
    price         DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
