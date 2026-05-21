<?= $this->extend('pegawai/layout.php') ?>

<?= $this->section('content') ?>

<input type="hidden" id="tanggal_keluar" name="tanggal_keluar" value="<?= $tanggal_keluar ?>">
<input type="hidden" id="jam_keluar" name="jam_keluar" value="<?= $jam_keluar ?>">
<div class="mb-3">
  <video id="webcam" autoplay playsinline style="width:320px;height:240px;border:1px solid #ccc;"></video>
  <div id="status" class="mt-2">Silakan kedipkan mata untuk verifikasi</div>
</div>
<div style="display: none;" id="my_result"></div>
<canvas id="capture_canvas" style="display:none;"></canvas>
<button class="btn btn-danger mt-2" id="ambil-foto-keluar" disabled>Keluar</button>

<script src="https://cdn.jsdelivr.net/npm/@mediapipe/camera_utils/camera_utils.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@mediapipe/drawing_utils/drawing_utils.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@mediapipe/face_mesh/face_mesh.js"></script>
<script>
// Cache DOM elements to prevent repeated queries during realtime processing
const videoElement = document.getElementById('webcam');
const statusElement = document.getElementById('status');
const canvasElement = document.getElementById('capture_canvas');
const buttonElement = document.getElementById('ambil-foto-keluar');
const canvasCtx = canvasElement.getContext('2d');

// Initialize MediaPipe FaceMesh
const faceMesh = new FaceMesh({
    locateFile: (file) => `https://cdn.jsdelivr.net/npm/@mediapipe/face_mesh/${file}`
});
faceMesh.setOptions({
    maxNumFaces: 1,
    minDetectionConfidence: 0.5,
    minTrackingConfidence: 0.5
});

// Eye landmark indices for left and right eyes (8 points each)
const leftEyeIndices = [33, 133, 159, 145, 153, 144, 163, 154];
const rightEyeIndices = [362, 263, 386, 374, 380, 373, 390, 381];

// Blink detection thresholds and state variables
// const OPEN_THRESHOLD = 0.22;     // EAR threshold for open eye
// const CLOSED_THRESHOLD = 0.15;   // EAR threshold for closed eye
// const BLINK_FRAME_THRESHOLD = 3; // Minimum frames required to confirm state change (debouncing)

const OPEN_THRESHOLD = 0.20;
const CLOSED_THRESHOLD = 0.18;
const BLINK_FRAME_THRESHOLD = 2;

let leftEyeLandmarks = [];
let rightEyeLandmarks = [];
let blinkState = 'init';           // State machine: init | open_seen | closed_seen
let verifiedBlink = false;         // Verification status
let openFrameCount = 0;            // Frame counter for debouncing open state
let closedFrameCount = 0;          // Frame counter for debouncing closed state
let cameraInitialized = false;     // Track camera initialization
let cameraInstance = null;         // Store camera instance for cleanup

// Video constraints optimized for both desktop and mobile
const videoConstraints = {
    onFrame: async () => {
        if (cameraInitialized && faceMesh) {
            await faceMesh.send({image: videoElement});
        }
    },
    width: {ideal: 320},
    height: {ideal: 240},
    facingMode: 'user'  // Mobile compatibility
};

// Initialize camera with error handling
try {
    cameraInstance = new Camera(videoElement, videoConstraints);
    faceMesh.onResults(onResults);
    cameraInstance.start();
    cameraInitialized = true;
} catch (error) {
    statusElement.textContent = 'Error: Kamera tidak dapat diakses. Periksa izin kamera browser.';
    console.error('Camera initialization error:', error);
    buttonElement.disabled = true;
}

function distance(a, b) {
    return Math.hypot(a.x - b.x, a.y - b.y);
}

// Calculate Eye Aspect Ratio (EAR) for determining eye open/closed state
function eyeAspectRatio(eye) {
    if (eye.length !== 8) return 0;
    const horizontal = distance(eye[0], eye[1]) || 1; // Horizontal distance between eye corners
    const vertical1 = distance(eye[2], eye[3]);       // First vertical distance
    const vertical2 = distance(eye[6], eye[5]);       // Second vertical distance
    const vertical3 = distance(eye[7], eye[4]);       // Third vertical distance
    // EAR formula: (vertical1 + vertical2 + vertical3) / (3 * horizontal)
    return (vertical1 + vertical2 + vertical3) / (3 * horizontal);
}

// Calculate average EAR from both eyes
function getAverageEAR() {
    const leftEAR = eyeAspectRatio(leftEyeLandmarks);
    const rightEAR = eyeAspectRatio(rightEyeLandmarks);
    return (leftEAR + rightEAR) / 2;
}

// Process MediaPipe FaceMesh results with improved false-positive detection
function onResults(results) {
    if (results.multiFaceLandmarks && results.multiFaceLandmarks.length > 0) {
        const landmarks = results.multiFaceLandmarks[0];
        leftEyeLandmarks = leftEyeIndices.map(index => landmarks[index]);
        rightEyeLandmarks = rightEyeIndices.map(index => landmarks[index]);

        const averageEAR = getAverageEAR();
        const isOpen = averageEAR > OPEN_THRESHOLD;
        const isClosed = averageEAR < CLOSED_THRESHOLD;

        // Debouncing: require multiple consecutive frames to confirm state change
        if (isOpen) {
            openFrameCount++;
            closedFrameCount = 0;
        } else if (isClosed) {
            closedFrameCount++;
            openFrameCount = 0;
        } else {
            openFrameCount = 0;
            closedFrameCount = 0;
        }

        // State machine: init → open_seen → closed_seen → verified
        if (!verifiedBlink) {
            if (blinkState === 'init' && openFrameCount >= BLINK_FRAME_THRESHOLD) {
                blinkState = 'open_seen';
            }
            if (blinkState === 'open_seen' && closedFrameCount >= BLINK_FRAME_THRESHOLD) {
                blinkState = 'closed_seen';
            }
            if (blinkState === 'closed_seen' && openFrameCount >= BLINK_FRAME_THRESHOLD) {
                verifiedBlink = true;
            }
        }

        // Update UI based on verification state
        if (verifiedBlink) {
            statusElement.textContent = 'Verifikasi berhasil, silakan klik Keluar';
            buttonElement.disabled = false;
        } else if (blinkState === 'closed_seen') {
            statusElement.textContent = 'Kedipan terdeteksi, buka mata kembali...';
        } else if (isClosed) {
            statusElement.textContent = ' Mata tertutup terdeteksi';
            buttonElement.disabled = true;
        } else if (isOpen) {
            statusElement.textContent = blinkState === 'init' 
                ? 'Mata terbuka — silakan kedipkan mata' 
                : 'Mata terbuka...';
            buttonElement.disabled = true;
        } else {
            statusElement.textContent = 'Wajah terdeteksi, silakan berkedip';
            buttonElement.disabled = true;
        }

    } else {
        // Face not detected: reset all states
        statusElement.textContent = 'Wajah tidak terdeteksi';
        leftEyeLandmarks = [];
        rightEyeLandmarks = [];
        blinkState = 'init';
        verifiedBlink = false;
        openFrameCount = 0;
        closedFrameCount = 0;
        buttonElement.disabled = true;
    }
}

function performCapture(type) {
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
}

// Manual capture event listener
buttonElement.addEventListener('click', function() {
    if (verifiedBlink) {
        performCapture('keluar');
    }
});

// Cleanup resources when page unloads (prevent memory leaks)
window.addEventListener('beforeunload', function() {
    if (cameraInstance) {
        try {
            cameraInstance.stop();
            cameraInitialized = false;
        } catch (error) {
            console.warn('Error stopping camera:', error);
        }
    }
});
</script>
    
<?= $this->endSection() ?>