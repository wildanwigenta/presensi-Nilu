<?= $this->extend('pegawai/layout.php') ?>

<?= $this->section('content') ?>

<input type="hidden" id="tanggal_keluar" name="tanggal_keluar" value="<?= $tanggal_keluar ?>">
<input type="hidden" id="jam_keluar" name="jam_keluar" value="<?= $jam_keluar ?>">
<div class="mb-3">
  <video id="webcam" autoplay playsinline style="width:320px;height:240px;border:1px solid #ccc;"></video>
  <div id="status" class="mt-2">Memuat kamera...</div>
</div>
<div style="display: none;" id="my_result"></div>
<canvas id="capture_canvas" style="display:none;"></canvas>
<button class="btn btn-danger mt-2" id="ambil-foto-keluar">Keluar</button>

<script src="https://cdn.jsdelivr.net/npm/@mediapipe/camera_utils/camera_utils.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@mediapipe/drawing_utils/drawing_utils.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@mediapipe/face_mesh/face_mesh.js"></script>
<script>
const videoElement = document.getElementById('webcam');
const statusElement = document.getElementById('status');
const canvasElement = document.getElementById('capture_canvas');
const canvasCtx = canvasElement.getContext('2d');

const faceMesh = new FaceMesh({
    locateFile: (file) => `https://cdn.jsdelivr.net/npm/@mediapipe/face_mesh/${file}`
});
faceMesh.setOptions({
    maxNumFaces: 1,
    minDetectionConfidence: 0.5,
    minTrackingConfidence: 0.5
});
faceMesh.onResults(onResults);

let leftEyeLandmarks = [];
let rightEyeLandmarks = [];
const leftEyeIndices = [33, 133, 159, 145, 153, 144, 163, 154];
const rightEyeIndices = [362, 263, 386, 374, 380, 373, 390, 381];

const camera = new Camera(videoElement, {
    onFrame: async () => {
        await faceMesh.send({image: videoElement});
    },
    width: 320,
    height: 240
});
camera.start();

function onResults(results) {
    if (results.multiFaceLandmarks && results.multiFaceLandmarks.length > 0) {
        statusElement.textContent = 'Wajah terdeteksi';
        const landmarks = results.multiFaceLandmarks[0];
        leftEyeLandmarks = leftEyeIndices.map(index => landmarks[index]);
        rightEyeLandmarks = rightEyeIndices.map(index => landmarks[index]);
    } else {
        statusElement.textContent = 'Wajah tidak terdeteksi';
        leftEyeLandmarks = [];
        rightEyeLandmarks = [];
    }
}

document.getElementById('ambil-foto-keluar').addEventListener('click', function() {
    canvasElement.width = videoElement.videoWidth || 320;
    canvasElement.height = videoElement.videoHeight || 240;
    canvasCtx.drawImage(videoElement, 0, 0, canvasElement.width, canvasElement.height);
    const data_uri = canvasElement.toDataURL('image/jpeg', 0.9);

    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        document.getElementById('my_result').innerHTML = '<img src="' + data_uri +'"/>';
        if (xhttp.readyState == 4 && xhttp.status == 200) {
            window.location.href = '<?= base_url('pegawai/home') ?>';
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