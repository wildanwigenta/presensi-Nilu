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

    const modelUrl = '/assets/models';

    try{
      statusEl.textContent = 'Memuat model wajah...';
      await faceapi.nets.tinyFaceDetector.loadFromUri(modelUrl);
      await faceapi.nets.faceLandmark68Net.loadFromUri(modelUrl);
      await faceapi.nets.faceRecognitionNet.loadFromUri(modelUrl);
    }catch(err){
      statusEl.textContent = 'Gagal memuat model face-api.';
      console.error('Model load error:', err);
      return;
    }

    const video = document.createElement('video');
    video.id = 'face_video';
    video.style.width = '320px';
    video.style.height = '240px';
    video.style.borderRadius = '8px';
    video.style.border = '1px solid #ccc';
    video.autoplay = true;
    video.muted = true;
    video.playsInline = true;
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

    try{
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

      const detections = await faceapi.detectAllFaces(video, new faceapi.TinyFaceDetectorOptions()).withFaceLandmarks().withFaceDescriptors();
      const resized = faceapi.resizeResults(detections, displaySize);
      const ctx = canvas.getContext('2d');
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      faceapi.draw.drawDetections(canvas, resized);
      faceapi.draw.drawFaceLandmarks(canvas, resized);

      if(detections && detections.length > 0){
        statusEl.textContent = 'Wajah terdeteksi';
        const descriptor = detections[0].descriptor;
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
