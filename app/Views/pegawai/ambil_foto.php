<?= $this->extend('pegawai/layout.php') ?>

<?= $this->section('content') ?>

<script>
  window.faceVerificationConfig = {
    verifyUrl: '<?= base_url('pegawai/verify_face') ?>',
    modelUrl: '<?= base_url('assets/models') ?>'
  };
</script>

<!-- face-api.js and loader for face recognition (models loaded from /assets/models) -->
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script src="<?= base_url('assets/js/face-recognition.js') ?>"></script>

<input type="hidden" id="id_pegawai" name="id_pegawai" value="<?= $id_pegawai ?>">
<input type="hidden" id="tanggal_masuk" name="tanggal_masuk" value="<?= $tanggal_masuk ?>">
<input type="hidden" id="jam_masuk" name="jam_masuk" value="<?= $jam_masuk ?>">
<input type="hidden" id="shift_id" name="shift_id" value="<?= $shift_id ?>">
<div id="my_camera" style="position: relative; width: 320px; height: 240px;"></div>
<div id="face_status" class="text-muted mt-2">Menunggu verifikasi wajah...</div>
<div id="face_match_status" class="fw-semibold mt-1"></div>
<div style="display: none;" id="my_result"></div>
<button class="btn btn-primary mt-2" id="ambil-foto" disabled>Masuk</button>

<script>
    document.getElementById('ambil-foto').addEventListener('click', function(){
        if(this.disabled){
            return;
        }

        const video = document.getElementById('face_video');
        if(!video){
            return;
        }

        const canvas = document.createElement('canvas');
        canvas.width = 320;
        canvas.height = 240;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        const data_uri = canvas.toDataURL('image/jpeg', 0.9);

        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
            document.getElementById('my_result').innerHTML = '<img src="' + data_uri +'"/>';
            if (xhttp.readyState == 4 && xhttp.status == 200) {
                window.location.href = '<?= base_url('pegawai/home') ?>';
            }
        };
        xhttp.open("POST", "<?= base_url('pegawai/presensi_masuk_aksi') ?>", true);
        xhttp.setRequestHeader("content-type", "application/x-www-form-urlencoded");
        xhttp.send(
            'foto_masuk=' + encodeURIComponent(data_uri) + 
            '&id_pegawai=' + encodeURIComponent(document.getElementById('id_pegawai').value) +
            '&tanggal_masuk=' + encodeURIComponent(document.getElementById('tanggal_masuk').value) +
            '&jam_masuk=' + encodeURIComponent(document.getElementById('jam_masuk').value) +
            '&shift_id=' + encodeURIComponent(document.getElementById('shift_id').value)
        );
    });


</script>
    
<?= $this->endSection() ?>