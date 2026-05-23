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
      console.log('Loading face-api models from:', baseUrl);
      try {
        await faceapi.nets.tinyFaceDetector.loadFromUri(baseUrl);
        console.log('✓ TinyFaceDetector loaded');
        await faceapi.nets.faceLandmark68Net.loadFromUri(baseUrl);
        console.log('✓ FaceLandmark68Net loaded');
        await faceapi.nets.faceRecognitionNet.loadFromUri(baseUrl);
        console.log('✓ FaceRecognitionNet loaded');
      } catch(err) {
        console.error('Model load failed:', err.message);
        throw err;
      }
    };

    let modelUrl = localModelUrl;
    let modelsLoaded = false;
    
    try{
      statusEl.textContent = 'Memuat model wajah dari lokal...';
      await loadModelSet(modelUrl);
      statusEl.textContent = 'Model wajah berhasil dimuat.';
      modelsLoaded = true;
      console.log('Models loaded successfully from local');
    }catch(err){
      console.warn('Local model load failed, trying fallback:', err);
      statusEl.textContent = 'Memuat model wajah dari server remote...';
      matchEl.textContent = 'Koneksi internet diperlukan untuk verifikasi wajah';
      modelUrl = fallbackModelUrl;
      try{
        await loadModelSet(modelUrl);
        statusEl.textContent = 'Model wajah berhasil dimuat dari server.';
        modelsLoaded = true;
        console.log('Models loaded successfully from remote');
      }catch(remoteErr){
        statusEl.textContent = 'Gagal memuat model wajah. Periksa koneksi internet.';
        matchEl.textContent = 'Error: ' + remoteErr.message;
        console.error('Load face-api models error:', remoteErr);
        button.disabled = true;
        return;
      }
    }

    if(!modelsLoaded) {
      statusEl.textContent = 'Gagal memuat model wajah.';
      button.disabled = true;
      return;
    }

    // Bersihkan elemen lama jika ada
    const existingVideo = cameraContainer.querySelector('video');
    const existingCanvas = cameraContainer.querySelector('canvas');
    if(existingVideo) existingVideo.remove();
    if(existingCanvas) existingCanvas.remove();

    // Buat elemen video
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
    cameraContainer.appendChild(video);

    // Aktifkan kamera
    let stream;
    try{
      if(window.faceRecognitionStream){
        window.faceRecognitionStream.getTracks().forEach(track => track.stop());
      }
      statusEl.textContent = 'Mengaktifkan kamera...';
      stream = await navigator.mediaDevices.getUserMedia({ 
        video: { 
          facingMode: 'user',
          width: { ideal: 320 },
          height: { ideal: 240 }
        } 
      });
      window.faceRecognitionStream = stream;
      video.srcObject = stream;

      // Tunggu video benar-benar siap sebelum lanjut
      await new Promise((resolve) => {
        video.onloadedmetadata = () => {
          video.play().then(resolve).catch(() => resolve());
        };
      });

      // Tunggu frame pertama siap
      await new Promise((resolve) => {
        if(video.readyState >= 2){
          resolve();
        } else {
          video.addEventListener('loadeddata', resolve, { once: true });
        }
      });

      statusEl.textContent = 'Kamera siap. Arahkan wajah ke kamera...';
      console.log('Camera started successfully');
    }catch(err){
      statusEl.textContent = 'Akses kamera ditolak atau tidak tersedia: ' + err.name;
      matchEl.textContent = 'Izinkan akses kamera untuk melanjutkan verifikasi wajah';
      console.error('Camera access error:', err);
      button.disabled = true;
      return;
    }

    // Buat canvas SETELAH kamera siap
    const canvas = document.createElement('canvas');
    canvas.id = 'face_canvas';
    canvas.style.position = 'absolute';
    canvas.style.top = '0';
    canvas.style.left = '0';
    canvas.style.width = '100%';
    canvas.style.height = '100%';
    canvas.style.pointerEvents = 'none';
    cameraContainer.appendChild(canvas);

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

    let displaySize = setDisplaySize();
    video.addEventListener('loadedmetadata', () => {
      displaySize = setDisplaySize();
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
        console.log('Sending face descriptor to server for verification...');
        const response = await fetch(verifyUrl, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ descriptor: Array.from(descriptor) })
        });

        if(!response.ok){
          throw new Error(`Server returned ${response.status}`);
        }

        const data = await response.json();
        console.log('Verification response:', data);
        
        if(data.verified){
          verified = true;
          lastServerDescriptor = descriptor;
          lastServerDistance = data.distance;
          setStatus('✓ Wajah sesuai akun', `Kemiripan: ${(100 - data.distance*100).toFixed(1)}%`, true);
        } else {
          verified = false;
          lastServerDistance = data.distance;
          if(data.distance !== null) {
            setStatus('✗ Wajah tidak sesuai akun', `Kemiripan: ${(100 - data.distance*100).toFixed(1)}% (perlu ≥ ${(100 - 55).toFixed(1)}%)`, false);
          } else {
            setStatus('✗ Wajah tidak sesuai akun', data.message || '', false);
          }
        }

        return data;
      }catch(err){
        console.error('Verify face error', err);
        verified = false;
        setStatus('✗ Gagal memverifikasi wajah', 'Periksa koneksi internet', false);
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

      try {
        const result = await faceapi.detectSingleFace(video, detectorOptions).withFaceLandmarks().withFaceDescriptor();
        const ctx = canvas.getContext('2d');
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        if(!result){
          setStatus('Wajah tidak terdeteksi. Arahkan wajah ke kamera.', '', false);
          if(verified){
            resetStatus();
          }
          return;
        }

        const resizedResult = faceapi.resizeResults(result, displaySize);
        faceapi.draw.drawDetections(canvas, resizedResult);
        faceapi.draw.drawFaceLandmarks(canvas, resizedResult);

        const descriptor = result.descriptor;
        
        if(!verified) {
          statusEl.textContent = 'Wajah terdeteksi, memverifikasi...';
        }

        const now = Date.now();
        const shouldVerify = !verified && !verifying && (
          !lastSentDescriptor || faceapi.euclideanDistance(lastSentDescriptor, descriptor) > 0.02 || (now - lastVerifyTime) > recheckDelay
        );

        if(verified && lastServerDescriptor){
          const stillMatch = faceapi.euclideanDistance(lastServerDescriptor, descriptor) <= 0.05;
          if(!stillMatch){
            console.log('Face no longer matches, re-verifying...');
            verified = false;
            setStatus('Wajah berubah, memverifikasi ulang...', '', false);
          }
        }

        if(!verified && shouldVerify){
          lastSentDescriptor = descriptor;
          lastVerifyTime = now;
          await compareWithServer(descriptor);
        }
      } catch(err) {
        console.error('processFrame error:', err);
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