(function(){
  async function initFaceRegistration(){
    const container = document.getElementById('face_registration_container');
    const statusEl = document.getElementById('face_status');
    const savedEl = document.getElementById('face_saved_status');
    const descriptorInput = document.getElementById('face_descriptor');

    if(!container || !statusEl || !descriptorInput){
      return;
    }

    if(typeof faceapi === 'undefined'){
      statusEl.textContent = 'face-api.js tidak ditemukan.';
      return;
    }

    const localModelUrl = window.faceRegistrationConfig?.modelUrl || new URL('/assets/models', window.location.origin).href;
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
      modelsLoaded = true;
      console.log('Models loaded successfully from local');
    }catch(err){
      console.warn('Local model load failed, trying fallback:', err);
      statusEl.textContent = 'Memuat model wajah dari server remote...';
      modelUrl = fallbackModelUrl;
      try{
        await loadModelSet(modelUrl);
        modelsLoaded = true;
        console.log('Models loaded successfully from remote');
      }catch(remoteErr){
        statusEl.textContent = 'Gagal memuat model wajah. Periksa koneksi internet.';
        console.error('Load face-api models error:', remoteErr);
        return;
      }
    }

    if(!modelsLoaded) {
      statusEl.textContent = 'Gagal memuat model wajah.';
      return;
    }

    container.style.position = 'relative';
    container.style.minWidth = '320px';
    container.style.minHeight = '260px';

    const existingVideo = container.querySelector('video');
    const existingCanvas = container.querySelector('canvas');
    if(existingVideo){
      existingVideo.remove();
    }
    if(existingCanvas){
      existingCanvas.remove();
    }

    const video = document.createElement('video');
    video.id = 'face_video';
    video.style.width = '320px';
    video.style.height = '240px';
    video.style.borderRadius = '8px';
    video.style.border = '1px solid #ccc';
    video.style.display = 'block';
    video.autoplay = true;
    video.muted = true;
    video.playsInline = true;
    video.setAttribute('playsinline', '');
    container.insertBefore(video, statusEl);

    const canvas = document.createElement('canvas');
    canvas.id = 'face_canvas';
    canvas.style.position = 'absolute';
    canvas.style.top = '0';
    canvas.style.left = '0';
    canvas.style.width = '320px';
    canvas.style.height = '240px';
    canvas.style.pointerEvents = 'none';
    container.insertBefore(canvas, statusEl);

    if (!navigator.mediaDevices || typeof navigator.mediaDevices.getUserMedia !== 'function') {
      statusEl.textContent = 'Browser tidak mendukung kamera.';
      return;
    }

    try{
      statusEl.textContent = 'Meminta izin kamera...';
      const stream = await navigator.mediaDevices.getUserMedia({ 
        video: { 
          facingMode: 'user',
          width: { ideal: 320 },
          height: { ideal: 240 }
        }
      });
      video.srcObject = stream;
      statusEl.textContent = 'Kamera aktif. Arahkan wajah ke kamera untuk registrasi...';
      console.log('Camera initialized for face registration');
      await new Promise((resolve) => {
        video.onloadedmetadata = () => {
          video.play().then(resolve).catch(() => resolve());
        };
      });
    }catch(err){
      statusEl.textContent = 'Akses kamera ditolak atau tidak tersedia: ' + err.name;
      console.error('Camera access error:', err);
      return;
    }

    const displaySize = { width: 320, height: 240 };
    faceapi.matchDimensions(canvas, displaySize);

    let faceSaved = false;

    statusEl.textContent = 'Mencari wajah...';

    const interval = setInterval(async ()=>{
      if(video.paused || video.ended){
        return;
      }

      try {
        const detection = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.5 })).withFaceLandmarks().withFaceDescriptor();
        const ctx = canvas.getContext('2d');
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        if(detection){
          const resized = faceapi.resizeResults(detection, displaySize);
          faceapi.draw.drawDetections(canvas, resized);
          faceapi.draw.drawFaceLandmarks(canvas, resized);
          
          statusEl.textContent = '✓ Wajah terdeteksi';
          const descriptor = detection.descriptor;
          const serialized = JSON.stringify(Array.from(descriptor));
          if(serialized !== descriptorInput.value){
            descriptorInput.value = serialized;
            savedEl.textContent = '✓ Wajah berhasil disimpan untuk registrasi.';
            faceSaved = true;
            console.log('Face descriptor saved for registration');
          }
        } else {
          statusEl.textContent = 'Wajah tidak terdeteksi. Arahkan wajah ke kamera.';
          if(!faceSaved){
            savedEl.textContent = '';
          }
        }
      } catch(err) {
        console.error('Detection error:', err);
      }
    }, 250);

    window.addEventListener('beforeunload', ()=>{
      clearInterval(interval);
      if(video && video.srcObject){
        const tracks = video.srcObject.getTracks();
        tracks.forEach(track => track.stop());
      }
    });
  }

  if(document.readyState !== 'loading'){
    initFaceRegistration();
  } else {
    document.addEventListener('DOMContentLoaded', initFaceRegistration);
  }
})();
