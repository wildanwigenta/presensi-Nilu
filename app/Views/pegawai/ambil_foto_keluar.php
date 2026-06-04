<?= $this->extend('pegawai/layout.php') ?>

<?= $this->section('content') ?>

<script>
    window.faceVerificationConfig = {
        verifyUrl: '<?= base_url('
        pegawai / verify_face ') ?>',
        modelUrl: '<?= base_url('
        assets / models ') ?>'
    };
</script>

<!-- face-api.js and loader for face recognition (models loaded from /assets/models) -->
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script src="<?= base_url('assets/js/face-recognition.js') ?>"></script>

<input type="hidden" id="tanggal_keluar" name="tanggal_keluar" value="<?= $tanggal_keluar ?>">
<input type="hidden" id="jam_keluar" name="jam_keluar" value="<?= $jam_keluar ?>">
<div id="my_camera" style="position: relative; width: 320px; height: 240px;"></div>
<div id="face_status" class="text-muted mt-2">Menunggu verifikasi wajah...</div>
<div id="face_match_status" class="fw-semibold mt-1"></div>
<div style="display: none;" id="my_result"></div>
<button class="btn btn-danger mt-2" id="ambil-foto-keluar" disabled>Keluar</button>

<script>
    document.getElementById('ambil-foto-keluar').addEventListener('click', function () {
        if (this.disabled) {
            return;
        }

        const video = document.getElementById('face_video');
        if (!video) {
            return;
        }

        const canvas = document.createElement('canvas');
        canvas.width = 320;
        canvas.height = 240;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        const data_uri = canvas.toDataURL('image/jpeg', 0.9);

        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function () {
            document.getElementById('my_result').innerHTML = '<img src="' + data_uri + '"/>';
            if (xhttp.readyState == 4 && xhttp.status == 200) {
                window.location.href = '<?= base_url('
                pegawai / home ') ?>';
            }
        };
        xhttp.open("POST", "<?= base_url('pegawai/presensi_keluar_aksi/' . $id_presensi) ?>", true);
        xhttp.setRequestHeader("content-type", "application/x-www-form-urlencoded");
        xhttp.send(
            'foto_keluar=' + encodeURIComponent(data_uri) +
            '&tanggal_keluar=' + encodeURIComponent(document.getElementById('tanggal_keluar').value) +
            '&jam_keluar=' + encodeURIComponent(document.getElementById('jam_keluar').value)
        );
    });
</script>

<?= $this->endSection() ?>