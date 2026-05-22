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
      await faceapi.nets.tinyFaceDetector.loadFromUri(baseUrl);
      await faceapi.nets.faceLandmark68Net.loadFromUri(baseUrl);
      await faceapi.nets.faceRecognitionNet.loadFromUri(baseUrl);
    };

    let modelUrl = localModelUrl;
    try{
      statusEl.textContent = 'Memuat model wajah...';
      await loadModelSet(modelUrl);
    }catch(err){
      console.warn('Local model load failed, trying fallback:', err);
      statusEl.textContent = 'Memuat model wajah dari server remote...';
      modelUrl = fallbackModelUrl;
      try{
        await loadModelSet(modelUrl);
      }catch(remoteErr){
        statusEl.textContent = 'Gagal memuat model face-api.';
        console.error('Remote model load error:', remoteErr);
        return;
      }
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
      const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' } });
      video.srcObject = stream;
      await video.play();
    }catch(err){
      statusEl.textContent = 'Akses kamera ditolak atau tidak tersedia.';
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

      const detection = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.5 })).withFaceLandmarks().withFaceDescriptor();
      const ctx = canvas.getContext('2d');
      ctx.clearRect(0, 0, canvas.width, canvas.height);

      if(detection){
        const resized = faceapi.resizeResults(detection, displaySize);
        faceapi.draw.drawDetections(canvas, resized);
        faceapi.draw.drawFaceLandmarks(canvas, resized);
      }

      if(detection){
        statusEl.textContent = 'Wajah terdeteksi';
        const descriptor = detection.descriptor;
        const serialized = JSON.stringify(Array.from(descriptor));
        if(serialized !== descriptorInput.value){
          descriptorInput.value = serialized;
          savedEl.textContent = 'Wajah berhasil disimpan.';
          faceSaved = true;
        }
      } else {
        statusEl.textContent = 'Wajah tidak terdeteksi';
        if(!faceSaved){
          savedEl.textContent = '';
        }
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
