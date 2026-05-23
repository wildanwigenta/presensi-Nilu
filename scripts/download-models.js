const fs = require('fs');
const path = require('path');
const https = require('https');

const args = process.argv.slice(2);

function getArg(name, fallback) {
  const index = args.indexOf(name);
  return index !== -1 ? args[index + 1] : fallback;
}

const baseUrl = (
  getArg(
    '--baseUrl',
    'https://justadudewhohacks.github.io/face-api.js/models/'
  )
).replace(/\/+$/, '') + '/';

const outDir = getArg(
  '--outDir',
  'public/assets/models'
);

const manifests = [
  'tiny_face_detector_model-weights_manifest.json',
  'face_landmark_68_model-weights_manifest.json',
  'face_recognition_model-weights_manifest.json'
];

if (!fs.existsSync(outDir)) {
  fs.mkdirSync(outDir, { recursive: true });
}

function download(url, dest) {
  return new Promise((resolve, reject) => {
    const file = fs.createWriteStream(dest);

    https.get(url, (response) => {
      if (response.statusCode !== 200) {
        reject(
          new Error(`HTTP ${response.statusCode} => ${url}`)
        );
        return;
      }

      response.pipe(file);

      file.on('finish', () => {
        file.close(resolve);
      });
    }).on('error', (err) => {
      fs.unlink(dest, () => {});
      reject(err);
    });
  });
}

async function fetchJson(url) {
  return new Promise((resolve, reject) => {
    https.get(url, (response) => {
      if (response.statusCode !== 200) {
        reject(
          new Error(`HTTP ${response.statusCode} => ${url}`)
        );
        return;
      }

      let data = '';

      response.on('data', chunk => {
        data += chunk;
      });

      response.on('end', () => {
        try {
          resolve(JSON.parse(data));
        } catch (err) {
          reject(err);
        }
      });
    }).on('error', reject);
  });
}

(async () => {
  console.log('Downloading models from:', baseUrl);

  for (const manifestName of manifests) {
    try {
      const manifestUrl = baseUrl + manifestName;

      console.log('\nFetching manifest:', manifestUrl);

      const manifest = await fetchJson(manifestUrl);

      const manifestPath = path.join(outDir, manifestName);

      fs.writeFileSync(
        manifestPath,
        JSON.stringify(manifest, null, 2)
      );

      console.log('Saved:', manifestName);

      const paths = manifest.weightsManifest
        ? manifest.weightsManifest.flatMap(g => g.paths)
        : manifest[0]?.paths || [];

      for (const fileName of paths) {
        const fileUrl = baseUrl + fileName;
        const filePath = path.join(outDir, fileName);

        console.log('Downloading:', fileName);

        await download(fileUrl, filePath);

        console.log('Saved:', fileName);
      }

    } catch (err) {
      console.error('FAILED:', manifestName);
      console.error(err.message);
    }
  }

  console.log('\nDone.');
})();