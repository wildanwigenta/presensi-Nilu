
# TECHNOLOGY STACK

## Frontend
- JavaScript
- Webcam API
- face-api.js

## Backend
- CodeIgniter 4

---

# WHY USING FACE-API.JS

face-api.js dipilih karena:
- ringan
- realtime
- berjalan langsung di browser
- tidak memerlukan training AI sendiri
- cocok untuk project skripsi
- mudah diintegrasikan dengan CodeIgniter 4

---

# IMPORTANT NOTES

## Fokus revisi:
- validasi wajah sesuai akun login

## Tidak mengubah:
- flow login
- flow validasi lokasi
- flow upload foto
- flow presensi existing
- flow multi shift

---

# SYSTEM FLOW BEFORE REVISION

Flow lama:

Login
→ Kamera aktif
→ Ambil foto
→ Presensi berhasil

Masalah:
- akun dapat digunakan orang lain.

---

# SYSTEM FLOW AFTER REVISION

Flow baru:

Login
→ Kamera aktif
→ Scan wajah realtime
→ Sistem membandingkan wajah dengan data akun login
→ Jika cocok:
    presensi berhasil
→ Jika tidak cocok:
    presensi ditolak

---

# IMPLEMENTATION CONCEPT

# 1. Face Registration

Setiap pegawai harus memiliki:
- data wajah referensi.

Saat admin menambahkan pegawai:
- sistem mengambil foto wajah pegawai
- lalu generate:
  # face descriptor

Descriptor tersebut disimpan ke database.

---

# 2. Face Verification

Saat presensi:
- sistem membuka webcam
- wajah realtime dideteksi
- sistem generate descriptor wajah sekarang
- descriptor dibandingkan dengan descriptor pegawai di database

Jika similarity cocok:
- presensi berhasil

Jika tidak cocok:
- presensi ditolak

---

# FACE DESCRIPTOR

Descriptor adalah:
- representasi numerik wajah.

Contoh:

```json
[-0.12, 0.44, 0.91, ...]
```

Biasanya:
- terdiri dari 128 angka vector.

Descriptor digunakan untuk:
- membandingkan kemiripan wajah.

---

# DATABASE CHANGES

## Tambahkan kolom pada tabel pegawai

Tambahkan:
- face_descriptor

Tipe data:
- LONGTEXT
- atau JSON

Tujuan:
- menyimpan descriptor wajah pegawai.

---

# FACE REGISTRATION FLOW

Flow registrasi wajah:

Admin tambah pegawai
→ Kamera aktif
→ Ambil foto wajah
→ Generate face descriptor
→ Simpan descriptor ke database

---

# FACE VERIFICATION FLOW

Flow presensi:

Pegawai login
→ Kamera aktif
→ Sistem scan wajah realtime
→ Generate descriptor realtime
→ Compare dengan descriptor database
→ Jika cocok:
    presensi berhasil
→ Jika tidak cocok:
    presensi ditolak

---

# FILES THAT WILL BE MODIFIED

## Views
- pegawai/ambil_foto.php
- pegawai/ambil_foto_keluar.php
- admin/data_pegawai/tambah.php
- admin/data_pegawai/edit.php

---

## JavaScript
Buat file baru:

```text
public/assets/js/face-recognition.js
```

---

# REQUIRED IMPLEMENTATION

# STEP 1 — Install face-api.js

Integrasikan:
- face-api.js
- model face recognition

---

# STEP 2 — Webcam Initialization

Saat halaman dibuka:
- webcam otomatis aktif.

Gunakan:
- navigator.mediaDevices.getUserMedia()

---

# STEP 3 — Load AI Models

Load model:
- TinyFaceDetector
- FaceLandmark68Net
- FaceRecognitionNet

Pastikan:
- model berjalan realtime di browser.

---

# STEP 4 — Face Detection

Sistem harus:
- mendeteksi wajah realtime dari webcam.

Jika wajah tidak ada:
- tampilkan pesan:
  "Wajah tidak terdeteksi"

---

# STEP 5 — Generate Face Descriptor

Saat wajah terdeteksi:
- generate face descriptor realtime.

---

# STEP 6 — Save Face Descriptor

Saat registrasi pegawai:
- simpan descriptor ke database.

Gunakan:
- JSON stringify jika diperlukan.

---

# STEP 7 — Compare Face Descriptor

Saat presensi:
- compare descriptor realtime
VS
- descriptor database

Gunakan:
- euclidean distance
- similarity threshold

---

# STEP 8 — Verification Result

Jika wajah cocok:
- tampilkan:
  "Wajah sesuai"

Jika tidak cocok:
- tampilkan:
  "Wajah tidak sesuai akun"

---

# STEP 9 — Lock Presensi Before Verification

Sebelum wajah cocok:
- tombol presensi disabled.

Setelah cocok:
- tombol aktif.

---

# STEP 10 — Integrate With Existing Presensi

Setelah verifikasi berhasil:
- gunakan flow presensi existing
- jangan ubah upload foto existing

---

# RECOMMENDED THRESHOLD

Threshold similarity yang disarankan:

```text
0.45 - 0.6
```

Semakin kecil:
- semakin ketat.

---

# OPTIONAL IMPROVEMENT

Disarankan:
# menggabungkan:
- face recognition
- blink detection

Tujuan:
- memastikan:
  - wajah sesuai akun
  - dan pengguna adalah manusia asli

---

# FINAL RECOMMENDED FLOW

Flow final yang direkomendasikan:

Login
→ Kamera aktif
→ Face recognition
→ Wajah cocok
→ Blink detection
→ Presensi berhasil

---

# IMPORTANT SYSTEM RULES

## 1. Jangan ubah flow presensi existing
Flow lama harus tetap berjalan.

---

## 2. Jangan ubah flow upload foto existing
Tetap gunakan:
- base64 capture existing.

---

## 3. Fokus pada face verification
Tidak perlu:
- training AI model sendiri
- Python backend
- Tensorflow backend
- server AI terpisah

---

# LIMITATION NOTES

Sistem ini:
- meningkatkan keamanan presensi
- mencegah penggunaan akun orang lain

Namun:
- belum termasuk anti spoofing tingkat enterprise
- lighting buruk dapat mempengaruhi akurasi
- kualitas webcam mempengaruhi hasil

Untuk level skripsi:
- implementasi ini sudah sangat baik dan realistis.

---

# EXPECTED OUTPUT

AI diharapkan membantu:
- integrasi face-api.js
- setup webcam
- realtime face detection
- generate face descriptor
- save descriptor ke database
- compare descriptor realtime
- validasi wajah sebelum presensi
- clean code JavaScript
- kompatibel dengan CodeIgniter 4

---

# CODING STANDARDS

Gunakan:
- clean code
- modular JavaScript
- best practice frontend
- struktur file rapi
- komentar seperlunya

---

# FINAL TARGET

Target akhir sistem:

1. Pegawai login
2. Kamera aktif
3. Sistem scan wajah realtime
4. Sistem membandingkan wajah dengan akun login
5. Jika wajah cocok:
   - presensi diizinkan
6. Jika wajah tidak cocok:
   - presensi ditolak
7. Presensi berhasil tersimpan
