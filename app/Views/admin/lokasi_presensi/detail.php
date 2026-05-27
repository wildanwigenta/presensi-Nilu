<?= $this->extend('admin/layout.php') ?>

<?= $this->section('content') ?>

<style>
    #map {
        height: 525px;
    }
</style>
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <table class="table">
                    <tr>
                        <td>Nama Lokasi</td>
                        <td>:</td>
                        <td><?= $lokasi_presensi['nama_lokasi'] ?></td>
                    </tr>
                    <tr>
                        <td>Alamat Lokasi</td>
                        <td>:</td>
                        <td><?= $lokasi_presensi['alamat_lokasi'] ?></td>
                    </tr>
                    <tr>
                        <td>Tipe Lokasi</td>
                        <td>:</td>
                        <td><?= $lokasi_presensi['tipe_lokasi'] ?></td>
                    </tr>
                    <tr>
                        <td>Latitude</td>
                        <td>:</td>
                        <td><?= $lokasi_presensi['latitude'] ?></td>
                    </tr>
                    <tr>
                        <td>Longitude</td>
                        <td>:</td>
                        <td><?= $lokasi_presensi['longitude'] ?></td>
                    </tr>
                    <tr>
                        <td>Radius</td>
                        <td>:</td>
                        <td><?= $lokasi_presensi['radius'] ?></td>
                    </tr>
                    <tr>
                        <td>Zona Waktu</td>
                        <td>:</td>
                        <td><?= $lokasi_presensi['zona_waktu'] ?></td>
                    </tr>
                    <tr>
                        <td>Jam Masuk</td>
                        <td>:</td>
                        <td><?= $lokasi_presensi['jam_masuk'] ?></td>
                    </tr>
                    <tr>
                        <td>Jam Pulang</td>
                        <td>:</td>
                        <td><?= $lokasi_presensi['jam_pulang'] ?></td>
                    </tr>
                </table>
                <a href="<?= base_url('admin/shifts/location/' . $lokasi_presensi['id']) ?>"
                    class="btn btn-primary">Lihat Shift di Lokasi Ini</a>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div id="map"></div>
    </div>

    <script>
        var map = L.map('map').setView([ < ? = $lokasi_presensi['latitude'] ? > , < ? = $lokasi_presensi['longitude'] ?
            >
        ], 13);

        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(map);

        var marker = L.marker([ < ? = $lokasi_presensi['latitude'] ? > , < ? = $lokasi_presensi['longitude'] ? > ])
            .addTo(map);
    </script>


    <?= $this->endSection() ?>