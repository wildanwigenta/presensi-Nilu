// Face recognition loader: loads face-api models and initializes webcam for realtime detection.
(function(){
  async function initFaceAPI(){
    if(typeof faceapi === 'undefined'){
      console.warn('face-api.js not loaded');
      return;
    }

    const modelUrl = '/assets/models';

    try{
      // Load models required for realtime detection and descriptor generation
      await faceapi.nets.tinyFaceDetector.loadFromUri(modelUrl);
      await faceapi.nets.faceLandmark68Net.loadFromUri(modelUrl);
      await faceapi.nets.faceRecognitionNet.loadFromUri(modelUrl);
    }catch(err){
      console.error('Error loading face-api models:', err);
      return;
    }

    // create or reuse a hidden video element inside #my_camera
    const cameraContainer = document.getElementById('my_camera');
    if(!cameraContainer) return;

    let video = document.getElementById('face_video');
    if(!video){
      video = document.createElement('video');
      video.id = 'face_video';
      video.width = 320;
      video.height = 240;
      video.style.display = 'block';
      video.style.maxWidth = '320px';
      video.style.maxHeight = '240px';
      video.autoplay = true;
      video.muted = true;
      video.playsInline = true;
      cameraContainer.appendChild(video);
    }

    // status indicator
    let statusEl = document.getElementById('face_status');
    if(!statusEl){
      statusEl = document.createElement('div');
      statusEl.id = 'face_status';
      statusEl.style.marginTop = '6px';
      statusEl.style.fontSize = '13px';
      cameraContainer.appendChild(statusEl);
    }

    try{
      const stream = await navigator.mediaDevices.getUserMedia({ video: true });
      video.srcObject = stream;
    }catch(err){
      statusEl.textContent = 'Camera akses ditolak atau tidak tersedia.';
      console.warn('getUserMedia error', err);
      return;
    }

    video.addEventListener('play', ()=>{
      const canvas = faceapi.createCanvasFromMedia(video);
      canvas.id = 'face_canvas';
      cameraContainer.appendChild(canvas);
      const displaySize = { width: video.width, height: video.height };
      faceapi.matchDimensions(canvas, displaySize);

      // run detection loop
      const interval = setInterval(async ()=>{
        if(video.paused || video.ended){
          return;
        }
        const detections = await faceapi.detectAllFaces(video, new faceapi.TinyFaceDetectorOptions()).withFaceLandmarks().withFaceDescriptors();
        const resized = faceapi.resizeResults(detections, displaySize);
        const ctx = canvas.getContext('2d');
        ctx.clearRect(0,0,canvas.width,canvas.height);
        faceapi.draw.drawDetections(canvas, resized);
        faceapi.draw.drawFaceLandmarks(canvas, resized);

        if(detections && detections.length > 0){
          statusEl.textContent = 'Wajah terdeteksi (' + detections.length + ')';
        } else {
          statusEl.textContent = 'Wajah tidak terdeteksi';
        }
      }, 250);

      // stop detection when page unloads
      window.addEventListener('beforeunload', ()=>{
        clearInterval(interval);
        if(video && video.srcObject){
          const tracks = video.srcObject.getTracks();
          tracks.forEach(t=>t.stop());
        }
      });
    });
  }

  // initialize when DOM ready
  if(document.readyState === 'complete' || document.readyState === 'interactive'){
    setTimeout(initFaceAPI, 200);
  } else {
    document.addEventListener('DOMContentLoaded', initFaceAPI);
  }
})();
