<?= $this->extend('pegawai/layout.php') ?>

<?= $this->section('content') ?>

<!-- Form untuk Filter dan Export -->
<form class="row g-3" method="GET" action="<?= base_url('pegawai/rekap_presensi') ?>">
    <div class="col-auto">
        <input type="date" class="form-control" name="filter_tanggal" value="<?= $filter_tanggal ?? '' ?>">
    </div>
    <div class="col-auto">
        <button type="submit" name="action" value="filter" class="btn btn-primary mb-3">Tampilkan</button>
    </div>
    <div class="col-auto">
        <button type="submit" name="action" value="excel" class="btn btn-success mb-3">Export Excel</button>
    </div>
</form>

<div class="table-responsive">
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Pegawai</th>
                <th>Tanggal</th>
                <th>Jam Masuk</th>
                <th>Jam Keluar</th>
                <th>Total Jam Kerja</th>
                <th>Total Keterlambatan</th>
            </tr>
        </thead>
        <tbody>
            <?php if(!empty($rekap_presensi)) : ?>
                <?php $no=1; foreach($rekap_presensi as $rekap) : ?>
                    <?php 
                    // Menghitung jumlah jam kerja
                    $total_jam_kerja = '0 Jam 0 Menit';
                    $total_terlambat = '0 Jam 0 Menit';
                    
                    if($rekap['jam_masuk'] != '00:00:00' && $rekap['jam_keluar'] != '00:00:00') {
                        $timestamp_jam_masuk = strtotime($rekap['tanggal_masuk'] . ' ' . $rekap['jam_masuk']);
                        $timestamp_jam_keluar = strtotime($rekap['tanggal_keluar'] . ' ' . $rekap['jam_keluar']);
                        $selisih = $timestamp_jam_keluar - $timestamp_jam_masuk;
                        if($selisih > 0) {
                            $jam = floor($selisih / 3600);
                            $selisih -= $jam * 3600;
                            $menit = floor($selisih / 60);
                            $total_jam_kerja = $jam . ' Jam ' . $menit . ' Menit';
                        }
                    }
                    
                    // Menghitung total keterlambatan
                    if(isset($rekap['jam_masuk_kantor']) && $rekap['jam_masuk'] != '00:00:00') {
                        $jam_masuk_real = strtotime($rekap['jam_masuk']);
                        $jam_masuk_kantor = strtotime($rekap['jam_masuk_kantor']);
                        $selisih_terlambat = $jam_masuk_real - $jam_masuk_kantor;
                        
                        if($selisih_terlambat > 0) {
                            $jam_terlambat = floor($selisih_terlambat / 3600);
                            $selisih_terlambat -= $jam_terlambat * 3600;
                            $menit_terlambat = floor($selisih_terlambat / 60);
                            $total_terlambat = $jam_terlambat . ' Jam ' . $menit_terlambat . ' Menit';
                        } else {
                            $total_terlambat = '<span class="badge bg-success">On Time</span>';
                        }
                    }
                    ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= esc($rekap['nama']) ?></td>
                        <td><?= date('d F Y', strtotime($rekap['tanggal_masuk'])) ?></td>
                        <td><?= esc($rekap['jam_masuk']) ?></td>
                        <td><?= esc($rekap['jam_keluar']) ?></td>
                        <td><?= $total_jam_kerja ?></td>
                        <td><?= $total_terlambat ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="7" class="text-center">Tidak ada data presensi</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?= $this->endSection() ?>