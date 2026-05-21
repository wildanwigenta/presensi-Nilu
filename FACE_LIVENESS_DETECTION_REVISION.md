# FACE LIVENESS DETECTION REVISION
## Sistem Presensi Cafe - CodeIgniter 4

---

# PROJECT OVERVIEW

Project ini adalah sistem presensi cafe berbasis CodeIgniter 4.

Sistem saat ini sudah memiliki fitur:
- Login pegawai
- Presensi masuk & keluar
- Validasi lokasi menggunakan latitude, longitude, dan radius
- Foto selfie saat presensi
- Sistem multi shift

Namun saat ini sistem masih memiliki kelemahan:
- presensi dapat diakali menggunakan foto wajah.

Contoh:
- pengguna menunjukkan foto wajah ke kamera,
- lalu sistem tetap menerima presensi.

Karena itu diperlukan:
# fitur liveness detection sederhana.

---

# OBJECTIVE

Membuat sistem validasi wajah agar:
- pengguna harus melakukan kedipan mata,
- sistem dapat membedakan wajah asli dan foto statis,
- presensi tidak dapat dilakukan hanya menggunakan gambar/foto.

---

# IMPLEMENTATION METHOD

Metode yang digunakan:
# Blink Detection menggunakan MediaPipe Face Mesh

---

# TECHNOLOGY STACK

## Frontend
- JavaScript
- Webcam API
- MediaPipe Face Mesh

## Backend
- CodeIgniter 4

---

# IMPORTANT NOTES

## Fokus revisi:
- hanya menambahkan validasi wajah asli (liveness detection sederhana)

## Tidak mengubah:
- flow login
- flow validasi lokasi
- flow upload foto
- flow presensi existing

---

# SYSTEM FLOW BEFORE REVISION

Flow lama:

Presensi
→ Kamera aktif
→ Ambil foto
→ Simpan presensi

Masalah:
- pengguna dapat menggunakan foto wajah.

---

# SYSTEM FLOW AFTER REVISION

Flow baru:

Presensi
→ Kamera aktif
→ Deteksi wajah realtime
→ Pengguna diminta berkedip
→ Sistem validasi kedipan
→ Foto diambil
→ Presensi berhasil

---

# IMPLEMENTATION CONCEPT

## 1. Kamera Aktif
Saat halaman ambil foto dibuka:
- webcam otomatis aktif.

---

## 2. Face Detection
Sistem mendeteksi:
- apakah terdapat wajah pada kamera.

Jika wajah tidak terdeteksi:
- presensi tidak dapat dilanjutkan.

---

## 3. Blink Detection
Sistem mendeteksi:
- mata terbuka,
- mata tertutup,
- lalu terbuka kembali.

Jika pola tersebut terdeteksi:
- sistem menganggap pengguna adalah manusia asli.

---

## 4. Verification Success
Jika kedipan berhasil:
- tombol presensi aktif,
- atau foto otomatis diambil.

---

# WHY USING MEDIAPIPE FACE MESH

MediaPipe Face Mesh dipilih karena:
- ringan,
- realtime,
- berjalan langsung di browser,
- tidak memerlukan training AI model,
- cocok untuk project skripsi.

---

# REQUIRED IMPLEMENTATION

# STEP 1 — Install MediaPipe

Gunakan MediaPipe Face Mesh.

AI harus membantu:
- integrasi library
- setup webcam
- setup realtime detection

---

# STEP 2 — Webcam Initialization

Saat halaman selfie dibuka:
- webcam otomatis aktif.

Gunakan:
- navigator.mediaDevices.getUserMedia()

---

# STEP 3 — Face Detection

Sistem harus:
- mendeteksi keberadaan wajah secara realtime.

Jika wajah tidak ada:
- tampilkan pesan:
  "Wajah tidak terdeteksi"

---

# STEP 4 — Eye Landmark Detection

Gunakan landmark mata dari MediaPipe:
- mata kiri
- mata kanan

Untuk menghitung:
- mata terbuka
- mata tertutup

---

# STEP 5 — Blink Detection Logic

Implementasikan logic:
- mata terbuka
- mata tertutup
- mata terbuka kembali

Jika pola berhasil:
- status verifikasi berhasil.

---

# STEP 6 — Verification Status

Tambahkan status UI:
- "Silakan kedipkan mata untuk verifikasi"
- "Verifikasi berhasil"

---

# STEP 7 — Lock Presensi Before Blink

Sebelum blink berhasil:
- tombol presensi harus disabled.

Setelah blink berhasil:
- tombol aktif.

---

# STEP 8 — Integrate With Existing Capture

Setelah verifikasi berhasil:
- gunakan flow capture foto existing,
- jangan ubah flow upload foto lama.

---

# FILES THAT WILL BE MODIFIED

## Views
- pegawai/ambil_foto.php
- pegawai/ambil_foto_keluar.php

---

## JavaScript
Buat file baru:

```text
public/assets/js/blink-detection.js
```

---

# UI REQUIREMENTS

Tambahkan:
- status deteksi wajah
- status blink verification
- loading sederhana jika diperlukan

Contoh:

```text
Wajah terdeteksi
Silakan kedipkan mata
```

Setelah berhasil:

```text
Verifikasi berhasil
```

---

# IMPORTANT SYSTEM RULES

## 1. Jangan ubah flow presensi existing
Sistem lama harus tetap berjalan.

---

## 2. Jangan ubah flow upload foto
Tetap gunakan:
- base64 capture existing.

---

## 3. Fokus hanya pada liveness detection sederhana
Tidak perlu:
- deepfake detection
- anti spoofing enterprise
- AI model training

---

# LIMITATION NOTES

Sistem ini:
- membantu mengurangi penggunaan foto statis,
- tetapi tidak menjamin anti fake 100%.

Karena:
- video wajah masih mungkin lolos,
- animasi wajah masih mungkin lolos.

Untuk level skripsi:
- blink detection sudah cukup baik sebagai implementasi AI/computer vision sederhana.

---

# EXPECTED OUTPUT

AI diharapkan membantu:
- integrasi MediaPipe
- setup webcam
- realtime face detection
- blink detection
- validasi sebelum presensi
- integrasi dengan flow existing
- clean code JavaScript
- kompatibel dengan CodeIgniter 4

---

# CODING STANDARDS

Gunakan:
- clean code
- modular JavaScript
- best practice frontend
- struktur file yang rapi
- komentar seperlunya

---

# FINAL TARGET

Target akhir sistem:

1. Pegawai membuka halaman presensi
2. Kamera aktif
3. Sistem mendeteksi wajah
4. Pegawai berkedip
5. Sistem memvalidasi kedipan
6. Tombol presensi aktif
7. Foto diambil
8. Presensi berhasil