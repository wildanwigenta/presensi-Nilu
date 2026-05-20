# MULTI SHIFT FEATURE REVISION
## Sistem Presensi Cafe - CodeIgniter 4

---

# PROJECT OVERVIEW

Project ini adalah sistem presensi cafe berbasis CodeIgniter 4.

Sistem saat ini sudah memiliki fitur:
- Login pegawai
- Presensi masuk & keluar
- Validasi lokasi menggunakan latitude, longitude, dan radius
- Foto selfie saat presensi
- Manajemen pegawai
- Manajemen lokasi presensi

Namun saat ini sistem masih menggunakan:
- 1 akun pegawai = 1 shift

Akibatnya:
- jika pegawai memiliki beberapa shift,
- maka harus dibuat beberapa akun berbeda.

Target revisi:
- 1 akun pegawai dapat memiliki banyak shift (multi shift).

---

# OBJECTIVE

Membuat sistem multi shift tanpa merusak flow presensi yang sudah ada.

Target:
- 1 akun pegawai dapat memiliki banyak shift
- Pegawai dapat memilih shift saat presensi
- Shift berbeda dapat digunakan dalam hari yang sama
- Shift mengikuti lokasi presensi masing-masing
- Presensi tetap menggunakan:
  - validasi lokasi
  - foto selfie
  - flow lama sistem

---

# EXISTING SYSTEM FLOW

Flow lama:

Login
→ Home Pegawai
→ Validasi lokasi
→ Ambil foto selfie
→ Presensi masuk

Masalah:
- shift masih bergantung pada akun berbeda.

---

# NEW SYSTEM FLOW

Flow baru:

Login
→ Home Pegawai
→ Pilih Shift
→ Validasi lokasi
→ Ambil foto selfie
→ Simpan presensi berdasarkan shift

---

# DATABASE STRUCTURE

## 1. Tabel `shifts`

Digunakan untuk menyimpan data shift berdasarkan lokasi presensi.

### Columns
- id
- lokasi_presensi_id
- nama_shift
- jam_masuk
- jam_keluar
- created_at
- updated_at

### Relationship
- 1 lokasi_presensi memiliki banyak shift

### Example Data

| id | lokasi_presensi_id | nama_shift | jam_masuk | jam_keluar |
|----|-------------------|------------|------------|-------------|
| 1 | 1 | Pagi | 08:00 | 16:00 |
| 2 | 1 | Sore | 16:00 | 00:00 |
| 3 | 1 | Midnight | 00:00 | 08:00 |

---

## 2. Tabel `pegawai_shift`

Digunakan untuk relasi many-to-many antara pegawai dan shift.

### Columns
- id
- pegawai_id
- shift_id
- created_at
- updated_at

### Relationship
- 1 pegawai dapat memiliki banyak shift
- 1 shift dapat dimiliki banyak pegawai

### Example Data

| id | pegawai_id | shift_id |
|----|-------------|-----------|
| 1 | 5 | 1 |
| 2 | 5 | 2 |

---

## 3. Tambahkan `shift_id` pada tabel `presensi`

Tambahkan kolom:
- shift_id

Tujuan:
- setiap data presensi mengetahui shift yang digunakan.

---

# DATABASE RELATIONSHIP

lokasi_presensi
↓
shifts
↓
pegawai_shift
↓
pegawai
↓
presensi

---

# IMPORTANT SYSTEM RULES

## 1. Shift mengikuti lokasi presensi
Setiap lokasi dapat memiliki banyak shift.

Contoh:
- Cafe A:
  - Pagi
  - Sore
  - Midnight

- Cafe B:
  - Pagi
  - Siang

---

## 2. Pegawai dapat memiliki banyak shift
Contoh:
- Tysha:
  - Shift Pagi
  - Shift Sore

Namun tetap menggunakan:
- 1 akun login.

---

## 3. Pegawai dapat presensi lebih dari sekali dalam sehari
Karena berbeda shift.

Maka validasi presensi harus berdasarkan:
- id_pegawai
- tanggal
- shift_id

Bukan hanya:
- id_pegawai
- tanggal

---

# REQUIRED IMPLEMENTATION

## STEP 1 — Migration

Buat migration untuk:
- tabel shifts
- tabel pegawai_shift
- tambah kolom shift_id pada presensi

Gunakan foreign key yang sesuai.

---

## STEP 2 — Models

Buat:
- ShiftModel
- PegawaiShiftModel

Gunakan struktur model CodeIgniter 4 yang benar.

---

# STEP 3 — Controller Modification

## File:
- Home.php (pegawai)

### Requirements

Saat pegawai login:
- ambil semua shift milik pegawai
- gunakan JOIN:
  - pegawai_shift
  - shifts

Kirim data shift ke view home.

---

# STEP 4 — View Modification

## File:
- pegawai/home.php

Tambahkan:
- dropdown/select shift sebelum presensi.

Contoh:

```html
<select name="shift_id">
    <option value="1">Pagi</option>
    <option value="2">Sore</option>
</select>
```

---

# STEP 5 — Presensi Masuk

## Modify:
- presensi_masuk()
- presensi_masuk_aksi()

### Requirements
- menerima shift_id dari form
- kirim shift_id ke halaman ambil_foto
- simpan shift_id ke tabel presensi

---

# STEP 6 — Presensi Validation

Sebelumnya:
- presensi dicek berdasarkan tanggal saja.

Sekarang:
- presensi harus dicek berdasarkan:
  - id_pegawai
  - tanggal
  - shift_id

Agar:
- pegawai tetap bisa presensi shift lain di hari yang sama.

---

# STEP 7 — Presensi Keluar

Pastikan:
- presensi keluar tetap terhubung dengan data presensi sesuai shift.

---

# STEP 8 — Admin Panel

Tambahkan fitur:
- CRUD shift
- shift berdasarkan lokasi presensi
- assign shift ke pegawai

---

# IMPORTANT NOTES

## DO NOT CHANGE
- flow validasi lokasi
- flow upload foto
- flow selfie
- sistem login existing

---

## FOCUS ONLY
- implementasi multi shift

---

## MIDNIGHT SHIFT
Untuk revisi ini:
- shift midnight cukup disimpan normal
- tidak perlu implementasi logic lintas hari kompleks

---

# CODING STANDARDS

Gunakan:
- clean code
- best practice CodeIgniter 4
- relational database yang rapi
- naming convention konsisten

---

# EXPECTED OUTPUT

AI diharapkan membantu:
- migration
- model
- controller
- query join
- view
- CRUD shift
- relasi database
- implementasi multi shift secara aman

Tanpa merusak sistem presensi yang sudah berjalan.