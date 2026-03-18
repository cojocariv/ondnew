-- PostgreSQL: schema pentru Cabinet Client (Google login + servicii + facturi + notificări)
-- Rulează o dată sau folosește /db/install_client_portal.php

CREATE TABLE IF NOT EXISTS client_users (
    id SERIAL PRIMARY KEY,
    google_sub VARCHAR(64) UNIQUE NOT NULL,
    email VARCHAR(190) UNIQUE NOT NULL,
    full_name VARCHAR(190) NOT NULL,
    picture_url TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login_at TIMESTAMP NULL
);

CREATE TABLE IF NOT EXISTS service_catalog (
    id SERIAL PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(190) NOT NULL,
    description TEXT NULL
);

CREATE TABLE IF NOT EXISTS client_services (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL REFERENCES client_users(id) ON DELETE CASCADE,
    service_id INT NOT NULL REFERENCES service_catalog(id) ON DELETE RESTRICT,
    package_name VARCHAR(190) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'active' CHECK (status IN ('active','suspended','cancelled')),
    started_at DATE DEFAULT CURRENT_DATE,
    next_billing_date DATE NULL,
    monthly_amount NUMERIC(12,2) NOT NULL DEFAULT 0,
    currency VARCHAR(10) NOT NULL DEFAULT 'MDL',
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_client_services_user ON client_services(user_id);

CREATE TABLE IF NOT EXISTS client_invoices (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL REFERENCES client_users(id) ON DELETE CASCADE,
    invoice_no VARCHAR(50) NULL,
    period_start DATE NULL,
    period_end DATE NULL,
    due_date DATE NULL,
    total_amount NUMERIC(12,2) NOT NULL DEFAULT 0,
    paid_amount NUMERIC(12,2) NOT NULL DEFAULT 0,
    currency VARCHAR(10) NOT NULL DEFAULT 'MDL',
    status VARCHAR(20) NOT NULL DEFAULT 'unpaid' CHECK (status IN ('unpaid','partial','paid','void')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_client_invoices_user ON client_invoices(user_id);

CREATE TABLE IF NOT EXISTS client_notification_prefs (
    user_id INT PRIMARY KEY REFERENCES client_users(id) ON DELETE CASCADE,
    email_billing BOOLEAN NOT NULL DEFAULT TRUE,
    email_service BOOLEAN NOT NULL DEFAULT TRUE,
    email_marketing BOOLEAN NOT NULL DEFAULT FALSE,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Cereri servicii noi (trimise adminului prin email + salvate în DB)
CREATE TABLE IF NOT EXISTS client_service_requests (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL REFERENCES client_users(id) ON DELETE CASCADE,
    service_id INT NOT NULL REFERENCES service_catalog(id) ON DELETE RESTRICT,
    period_text VARCHAR(190) NOT NULL,
    price_per_unit NUMERIC(12,2) NOT NULL DEFAULT 0,
    amount NUMERIC(12,2) NOT NULL DEFAULT 0,
    total_amount NUMERIC(12,2) NOT NULL DEFAULT 0,
    currency VARCHAR(10) NOT NULL DEFAULT 'MDL',
    status VARCHAR(20) NOT NULL DEFAULT 'pending' CHECK (status IN ('pending','processed','cancelled')),
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Seed catalog minimal (idempotent)
INSERT INTO service_catalog (code, name, description)
VALUES
    ('hosting', 'Găzduire web', 'Găzduire web / site-uri'),
    ('vps', 'VPS / VDS', 'Server virtual'),
    ('1c', 'Hosting 1C', '1C în cloud'),
    ('m365', 'Microsoft 365', 'Licențe și configurare')
ON CONFLICT (code) DO NOTHING;

