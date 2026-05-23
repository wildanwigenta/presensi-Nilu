// Face recognition loader: loads face-api models, verifies login face descriptor, and enables presensi button.
(function(){
  async function initFaceAPI(){
    const cameraContainer = document.getElementById('my_camera');
    const statusEl = document.getElementById('face_status');
    const matchEl = document.getElementById('face_match_status');
    const button = document.getElementById('ambil-foto') || document.getElementById('ambil-foto-keluar');

    if(!cameraContainer || !statusEl || !button){
      return;
    }

    button.disabled = true;
    statusEl.textContent = 'Memuat model wajah...';
    matchEl.textContent = '';

    if(typeof faceapi === 'undefined'){
      statusEl.textContent = 'face-api.js tidak ditemukan.';
      return;
    }

    const verifyUrl = window.faceVerificationConfig?.verifyUrl || '/pegawai/verify_face';
    const localModelUrl = window.faceVerificationConfig?.modelUrl || new URL('/assets/models', window.location.origin).href;
    const fallbackModelUrl = 'https://justadudewhohacks.github.io/face-api.js/models';

    const loadModelSet = async (baseUrl) => {
      await faceapi.nets.tinyFaceDetector.loadFromUri(baseUrl);
      await faceapi.nets.faceLandmark68Net.loadFromUri(baseUrl);
      await faceapi.nets.faceRecognitionNet.loadFromUri(baseUrl);
    };

    let modelUrl = localModelUrl;
    try{
      await loadModelSet(modelUrl);
      statusEl.textContent = 'Model face-api dimuat.';
    }catch(err){
      console.warn('Local model load failed, trying fallback:', err);
      statusEl.textContent = 'Memuat model face-api dari server remote...';
      modelUrl = fallbackModelUrl;
      try{
        await loadModelSet(modelUrl);
        statusEl.textContent = 'Model face-api dimuat dari server remote.';
      }catch(remoteErr){
        statusEl.textContent = 'Gagal memuat model face-api.';
        console.error('Load face-api models error:', remoteErr);
        return;
      }
    }

    if(cameraContainer.querySelector('video') || cameraContainer.querySelector('canvas')){
      statusEl.textContent = 'Kamera sudah aktif.';
    }

    const video = document.createElement('video');
    video.id = 'face_video';
    video.style.width = '100%';
    video.style.height = '100%';
    video.style.borderRadius = '8px';
    video.style.border = '1px solid #ddd';
    video.style.display = 'block';
    video.autoplay = true;
    video.muted = true;
    video.playsInline = true;

    const existingVideo = cameraContainer.querySelector('video');
    const existingCanvas = cameraContainer.querySelector('canvas');
    if(existingVideo){
      existingVideo.remove();
    }
    if(existingCanvas){
      existingCanvas.remove();
    }

    cameraContainer.appendChild(video);

    const canvas = faceapi.createCanvasFromMedia(video);
    canvas.id = 'face_canvas';
    canvas.style.position = 'absolute';
    canvas.style.top = '0';
    canvas.style.left = '0';
    canvas.style.width = '100%';
    canvas.style.height = '100%';
    canvas.style.pointerEvents = 'none';
    cameraContainer.appendChild(canvas);

    let stream;
    try{
      if(window.faceRecognitionStream){
        window.faceRecognitionStream.getTracks().forEach(track => track.stop());
      }
      stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' } });
      window.faceRecognitionStream = stream;
      video.srcObject = stream;
      statusEl.textContent = 'Mengaktifkan kamera...';
      await video.play();
    }catch(err){
      statusEl.textContent = 'Akses kamera ditolak atau tidak tersedia.';
      console.error('Camera access error:', err);
      return;
    }

    const setDisplaySize = () => {
      const displaySize = {
        width: video.videoWidth || cameraContainer.clientWidth || 320,
        height: video.videoHeight || cameraContainer.clientHeight || 240
      };
      canvas.width = displaySize.width;
      canvas.height = displaySize.height;
      faceapi.matchDimensions(canvas, displaySize);
      return displaySize;
    };

    const displaySize = setDisplaySize();
    video.addEventListener('loadedmetadata', () => {
      setDisplaySize();
    });

    let verifying = false;
    let verified = false;
    let lastSentDescriptor = null;
    let lastServerDescriptor = null;
    let lastServerDistance = null;
    let lastVerifyTime = 0;

    const resetStatus = () => {
      verified = false;
      button.disabled = true;
      matchEl.textContent = '';
    };

    const setStatus = (message, matchMessage, valid) => {
      statusEl.textContent = message;
      matchEl.textContent = matchMessage || '';
      button.disabled = !valid;
    };

    const compareWithServer = async (descriptor) => {
      if(verifying) return null;
      verifying = true;
      try{
        const response = await fetch(verifyUrl, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ descriptor: Array.from(descriptor) })
        });

        if(!response.ok){
          throw new Error(`Server returned ${response.status}`);
        }

        const data = await response.json();
        if(data.verified){
          verified = true;
          lastServerDescriptor = descriptor;
          lastServerDistance = data.distance;
          setStatus('Wajah sesuai akun', `Distance: ${data.distance.toFixed(4)}`, true);
        } else {
          verified = false;
          lastServerDistance = data.distance;
          setStatus('Wajah tidak sesuai akun', data.distance !== null ? `Distance: ${data.distance.toFixed(4)}` : '', false);
        }

        return data;
      }catch(err){
        console.error('Verify face error', err);
        verified = false;
        setStatus('Gagal memverifikasi wajah', '', false);
        return null;
      } finally {
        verifying = false;
      }
    };

    const detectorOptions = new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.5 });
    const recheckDelay = 1200;

    const processFrame = async () => {
      if(video.paused || video.ended){
        return;
      }

      const result = await faceapi.detectSingleFace(video, detectorOptions).withFaceLandmarks().withFaceDescriptor();
      const ctx = canvas.getContext('2d');
      ctx.clearRect(0, 0, canvas.width, canvas.height);

      if(!result){
        setStatus('Wajah tidak terdeteksi', '', false);
        if(verified){
          resetStatus();
        }
        return;
      }

      const resizedResult = faceapi.resizeResults(result, displaySize);
      faceapi.draw.drawDetections(canvas, resizedResult);
      faceapi.draw.drawFaceLandmarks(canvas, resizedResult);

      const descriptor = result.descriptor;
      statusEl.textContent = verified ? 'Wajah sesuai akun' : 'Wajah terdeteksi, memverifikasi...';

      const now = Date.now();
      const shouldVerify = !verified && !verifying && (
        !lastSentDescriptor || faceapi.euclideanDistance(lastSentDescriptor, descriptor) > 0.02 || (now - lastVerifyTime) > recheckDelay
      );

      if(verified && lastServerDescriptor){
        const stillMatch = faceapi.euclideanDistance(lastServerDescriptor, descriptor) <= 0.05;
        if(!stillMatch){
          verified = false;
          setStatus('Wajah terdeteksi, memverifikasi ulang...', '', false);
        }
      }

      if(!verified && shouldVerify){
        lastSentDescriptor = descriptor;
        lastVerifyTime = now;
        await compareWithServer(descriptor);
      }
    };

    const interval = setInterval(processFrame, 400);

    window.addEventListener('beforeunload', ()=>{
      clearInterval(interval);
      if(window.faceRecognitionStream){
        window.faceRecognitionStream.getTracks().forEach(track => track.stop());
        window.faceRecognitionStream = null;
      }
    });
  }

  if(document.readyState === 'complete' || document.readyState === 'interactive'){
    setTimeout(initFaceAPI, 200);
  } else {
    document.addEventListener('DOMContentLoaded', initFaceAPI);
  }
})();
