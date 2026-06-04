<?= $this->extend('pegawai/layout.php') ?>

<?= $this->section('content') ?>

<style>
  .parent-clock {
    display: grid;
    grid-template-columns: auto auto auto auto auto;
    font-size: 35px;
    font-weight: bold;
    justify-content: center;
  }

  #map {
    height: 525px;
    width: 600px;
    margin: auto;
  }
</style>

<div class="row mb-3">
  <div class="col-md-2"></div>

  <div class="col-md-4">
    <div class="card h-100">
      <div class="card-header">Presensi Masuk</div>

      <?php if ($open_presensi > 0) : ?>
      <div class="card-body">
        <h5 class="text-center">Anda Telah Melakukan Presensi Masuk
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path
              d="M15.5071 10.5245C15.8 10.2316 15.8 9.75674 15.5071 9.46384C15.2142 9.17095 14.7393 9.17095 14.4464 9.46384L10.9649 12.9454L9.55359 11.5341C9.2607 11.2412 8.78582 11.2412 8.49293 11.5341C8.20004 11.827 8.20004 12.3019 8.49294 12.5947L10.4346 14.5364C10.7275 14.8293 11.2023 14.8292 11.4952 14.5364L15.5071 10.5245Z"
              fill="#323544" />
            <path fill-rule="evenodd" clip-rule="evenodd"
              d="M12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2ZM3.5 12C3.5 7.30558 7.30558 3.5 12 3.5C16.6944 3.5 20.5 7.30558 20.5 12C20.5 16.6944 16.6944 20.5 12 20.5C7.30558 20.5 3.5 16.6944 3.5 12Z"
              fill="#323544" />
          </svg>
        </h5>
      </div>

      <?php elseif ($cek_presensi_keluar > 0) : ?>
      <div class="card-body">
        <h5 class="text-center">Anda Telah Melakukan Presensi Masuk
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path
              d="M15.5071 10.5245C15.8 10.2316 15.8 9.75674 15.5071 9.46384C15.2142 9.17095 14.7393 9.17095 14.4464 9.46384L10.9649 12.9454L9.55359 11.5341C9.2607 11.2412 8.78582 11.2412 8.49293 11.5341C8.20004 11.827 8.20004 12.3019 8.49294 12.5947L10.4346 14.5364C10.7275 14.8293 11.2023 14.8292 11.4952 14.5364L15.5071 10.5245Z"
              fill="#323544" />
            <path fill-rule="evenodd" clip-rule="evenodd"
              d="M12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2ZM3.5 12C3.5 7.30558 7.30558 3.5 12 3.5C16.6944 3.5 20.5 7.30558 20.5 12C20.5 16.6944 16.6944 20.5 12 20.5C7.30558 20.5 3.5 16.6944 3.5 12Z"
              fill="#323544" />
          </svg>
        </h5>
      </div>

      <?php else : ?>
      <div class="card-body text-center">
        <div class="fw-bold"><?= date('d F Y') ?></div>
        <div class="parent-clock">
          <div id="jam-masuk"></div>
          <div>:</div>
          <div id="menit-masuk"></div>
          <div>:</div>
          <div id="detik-masuk"></div>
        </div>
        <form method="POST" action="<?= base_url('pegawai/presensi_masuk') ?>">
          <?php
              if ($lokasi_presensi['zona_waktu'] == 'WIB') {
                date_default_timezone_set('Asia/Jakarta');
              } elseif ($lokasi_presensi['zona_waktu'] == 'WITA') {
                date_default_timezone_set('Asia/Makassar');
              } elseif ($lokasi_presensi['zona_waktu'] == 'WIT') {
                date_default_timezone_set('Asia/Jayapura');
              }
            ?>
          <div class="mb-3 text-start">
            <label for="shift_id" class="form-label">Pilih Shift</label>
            <select class="form-select" id="shift_id" name="shift_id" required>
              <option value="" selected disabled>Pilih shift</option>
              <?php foreach ($shifts as $shift) : ?>
              <option value="<?= $shift['id'] ?>">
                <?= esc($shift['nama_shift']) ?>
                <?php if (!empty($shift['jam_masuk']) || !empty($shift['jam_keluar'])) : ?>
                (<?= esc($shift['jam_masuk']) ?> - <?= esc($shift['jam_keluar']) ?>)
                <?php endif; ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <input type="hidden" name="latitude_outlet" value="<?= $lokasi_presensi['latitude'] ?>">
          <input type="hidden" name="longitude_outlet" value="<?= $lokasi_presensi['longitude'] ?>">
          <input type="hidden" name="radius" value="<?= $lokasi_presensi['radius'] ?>">
          <input type="hidden" name="latitude_pegawai" id="latitude_pegawai">
          <input type="hidden" name="longitude_pegawai" id="longitude_pegawai">
          <input type="hidden" name="tanggal_masuk" value="<?= date('Y-m-d') ?>">
          <input type="hidden" name="jam_masuk" value="<?= date('H:i:s') ?>">
          <input type="hidden" name="id_pegawai" value="<?= session()->get('id_pegawai') ?>">
          <button class="btn btn-success mt-3">Masuk</button>
        </form>
      </div>
      <?php endif; ?>

    </div>
  </div>

  <div class="col-md-4">
    <div class="card">
      <div class="card-header">Presensi Keluar</div>

      <?php if ($cek_presensi < 1) : ?>
      <div class="card-body">
        <h5 class="text-center">Anda Belum Melakukan Presensi Masuk</h5>
      </div>

      <?php elseif ($cek_presensi_keluar > 0) : ?>
      <div class="card-body">
        <h5 class="text-center">Anda Telah Melakukan Presensi Keluar
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path
              d="M15.5071 10.5245C15.8 10.2316 15.8 9.75674 15.5071 9.46384C15.2142 9.17095 14.7393 9.17095 14.4464 9.46384L10.9649 12.9454L9.55359 11.5341C9.2607 11.2412 8.78582 11.2412 8.49293 11.5341C8.20004 11.827 8.20004 12.3019 8.49294 12.5947L10.4346 14.5364C10.7275 14.8293 11.2023 14.8292 11.4952 14.5364L15.5071 10.5245Z"
              fill="#323544" />
            <path fill-rule="evenodd" clip-rule="evenodd"
              d="M12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2ZM3.5 12C3.5 7.30558 7.30558 3.5 12 3.5C16.6944 3.5 20.5 7.30558 20.5 12C20.5 16.6944 16.6944 20.5 12 20.5C7.30558 20.5 3.5 16.6944 3.5 12Z"
              fill="#323544" />
          </svg>
        </h5>
      </div>

      <?php elseif ($open_presensi > 0 && $ambil_presensi_masuk) : ?>
      <div class="card-body text-center">
        <div class="fw-bold"><?= date('d F Y') ?></div>
        <div class="parent-clock">
          <div id="jam-keluar"></div>
          <div>:</div>
          <div id="menit-keluar"></div>
          <div>:</div>
          <div id="detik-keluar"></div>
        </div>
        <form method="POST" action="<?= base_url('pegawai/presensi_keluar/' . $ambil_presensi_masuk['id']) ?>">
          <?php
              if ($lokasi_presensi['zona_waktu'] == 'WIB') {
                date_default_timezone_set('Asia/Jakarta');
              } elseif ($lokasi_presensi['zona_waktu'] == 'WITA') {
                date_default_timezone_set('Asia/Makassar');
              } elseif ($lokasi_presensi['zona_waktu'] == 'WIT') {
                date_default_timezone_set('Asia/Jayapura');
              }
            ?>
          <input type="hidden" name="latitude_outlet" value="<?= $lokasi_presensi['latitude'] ?>">
          <input type="hidden" name="longitude_outlet" value="<?= $lokasi_presensi['longitude'] ?>">
          <input type="hidden" name="radius" value="<?= $lokasi_presensi['radius'] ?>">
          <input type="hidden" name="latitude_pegawai" id="latitude_pegawai">
          <input type="hidden" name="longitude_pegawai" id="longitude_pegawai">
          <input type="hidden" name="tanggal_keluar" value="<?= date('Y-m-d') ?>">
          <input type="hidden" name="jam_keluar" value="<?= date('H:i:s') ?>">
          <button class="btn btn-danger mt-3">Keluar</button>
        </form>
      </div>

      <?php else : ?>
      <div class="card-body">
        <h5 class="text-center">Anda Belum Melakukan Presensi Masuk</h5>
      </div>
      <?php endif; ?>

    </div>
  </div>

  <div class="col-md-2"></div>
</div>

<div id="map"></div>

<script>
  window.setInterval("waktuMasuk()", 1000);

  function waktuMasuk() {
    const waktu = new Date();
    const el = document.getElementById("jam-masuk");
    if (el) {
      el.innerHTML = formatWaktu(waktu.getHours());
      document.getElementById("menit-masuk").innerHTML = formatWaktu(waktu.getMinutes());
      document.getElementById("detik-masuk").innerHTML = formatWaktu(waktu.getSeconds());
    }
  }

  window.setInterval("waktuKeluar()", 1000);

  function waktuKeluar() {
    const waktu = new Date();
    const el = document.getElementById("jam-keluar");
    if (el) {
      el.innerHTML = formatWaktu(waktu.getHours());
      document.getElementById("menit-keluar").innerHTML = formatWaktu(waktu.getMinutes());
      document.getElementById("detik-keluar").innerHTML = formatWaktu(waktu.getSeconds());
    }
  }

  function formatWaktu(waktu) {
    return waktu < 10 ? "0" + waktu : waktu;
  }

  getLocation();

  function getLocation() {
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(showPosition);
    } else {
      alert("Browser anda tidak mendukung Geolocation");
    }
  }

  function showPosition(position) {
    var latitude_pegawai = position.coords.latitude;
    var longitude_pegawai = position.coords.longitude;

    const latEl = document.getElementById('latitude_pegawai');
    const lngEl = document.getElementById('longitude_pegawai');
    if (latEl) latEl.value = latitude_pegawai;
    if (lngEl) lngEl.value = longitude_pegawai;

    initMap(latitude_pegawai, longitude_pegawai);
  }

  function initMap(latitude_pegawai, longitude_pegawai) {
    var map = L.map('map').setView([ < ? = $lokasi_presensi['latitude'] ? > , < ? = $lokasi_presensi['longitude'] ? > ],
      13);

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(map);

    L.marker([ < ? = $lokasi_presensi['latitude'] ? > , < ? = $lokasi_presensi['longitude'] ? > ]).addTo(map);

    L.circle([latitude_pegawai, longitude_pegawai], {
      color: 'red',
      fillColor: '#f03',
      fillOpacity: 0.5,
      radius: 300
    }).addTo(map);
  }
</script>

<?= $this->endSection() ?>