
CREATE DATABASE IF NOT EXISTS peminjaman_alat_2401020163
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE peminjaman_alat_2401020163;

CREATE TABLE alat (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_alat VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE peminjaman_alat (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kode_peminjaman CHAR(10) NOT NULL UNIQUE,
    nama_peminjam VARCHAR(100) NOT NULL,
    nomor_identitas VARCHAR(30) NOT NULL UNIQUE,
    jumlah_alat TINYINT UNSIGNED NOT NULL,
    alat_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    CONSTRAINT chk_jumlah_alat
    CHECK (jumlah_alat BETWEEN 1 AND 10),

    CONSTRAINT fk_peminjaman_alat
    FOREIGN KEY (alat_id)
    REFERENCES alat(id)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
) ENGINE=InnoDB;

INSERT INTO alat (id, nama_alat) VALUES
(1, 'Kamera'),
(2, 'Proyektor');

INSERT INTO peminjaman_alat
(
    kode_peminjaman,
    nama_peminjam,
    nomor_identitas,
    jumlah_alat,
    alat_id,
    created_at,
    updated_at
)
VALUES
(
    'PM00000001',
    'Rina Kurnia',
    'MHS-2201001',
    2,
    1,
    NOW(),
    NOW()
),
(
    'PM00000002',
    'Dedi Pranata',
    'MHS-2201002',
    1,
    2,
    NOW(),
    NOW()
),
(
    'PM00000003',
    'Mira Ananda',
    'MHS-2201003',
    3,
    1,
    NOW(),
    NOW()
);
