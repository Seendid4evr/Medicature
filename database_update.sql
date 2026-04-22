USE medicature;

CREATE TABLE IF NOT EXISTS bd_generics (
    id INT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    drug_class VARCHAR(255),
    indication TEXT,
    indication_description TEXT,
    therapeutic_class_description TEXT,
    pharmacology_description TEXT,
    dosage_description TEXT,
    administration_description TEXT,
    interaction_description TEXT,
    contraindications_description TEXT,
    side_effects_description TEXT,
    pregnancy_and_lactation_description TEXT,
    precautions_description TEXT,
    pediatric_usage_description TEXT,
    overdose_effects_description TEXT,
    duration_of_treatment_description TEXT,
    reconstitution_description TEXT,
    storage_conditions_description TEXT,
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS bd_medicines (
    id INT PRIMARY KEY,
    brand_name VARCHAR(255) NOT NULL,
    type VARCHAR(100),
    dosage_form VARCHAR(255),
    generic VARCHAR(255),
    strength VARCHAR(255),
    manufacturer VARCHAR(255),
    package_container TEXT,
    package_size TEXT,
    INDEX idx_brand_name (brand_name),
    INDEX idx_generic (generic)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE medicines
ADD COLUMN bd_medicine_id INT NULL DEFAULT NULL AFTER user_id,
ADD FOREIGN KEY (bd_medicine_id) REFERENCES bd_medicines(id) ON DELETE SET NULL;
