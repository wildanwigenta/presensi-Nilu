<?= $this->extend('pegawai/layout.php') ?>
<?= $this->section('content') ?>

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
                <th>Shift</th>
                <th>Tanggal</th>
                <th>Jam Masuk</th>
                <th>Jam Keluar</th>
                <th>Total Jam Kerja</th>
                <th>Total Keterlambatan</th>
            </tr>
        </thead>
        <tbody>
            <?php if(!empty($rekap_presensi)) : ?>
            <?php $no = 1; foreach($rekap_presensi as $rekap) : ?>
            <?php
                    // Hitung total jam kerja
                    $total_jam_kerja = '-';
                    if (!empty($rekap['jam_masuk']) && !empty($rekap['jam_keluar']) 
                    && $rekap['jam_keluar'] !== null
                    && $rekap['tanggal_keluar'] !== null) {
                        $masuk  = new DateTime($rekap['tanggal_masuk'] . ' ' . $rekap['jam_masuk']);
                        $keluar = new DateTime(($rekap['tanggal_keluar'] ?: $rekap['tanggal_masuk']) . ' ' . $rekap['jam_keluar']);
                        
                        // Jika keluar lebih kecil dari masuk, berarti melewati tengah malam
                        if ($keluar < $masuk) {
                            $keluar->modify('+1 day');
                        }
                        
                        $diff = $masuk->diff($keluar);
                        $total_hours = ($diff->days * 24) + $diff->h;
                        $total_jam_kerja = $total_hours . ' jam ' . $diff->i . ' menit';
                        if ($diff->s > 0) {
                            $total_jam_kerja .= ' ' . $diff->s . ' detik';
                        }
                    }

                    // Hitung keterlambatan
                    $total_terlambat = '-';
                    if (!empty($rekap['jam_masuk']) && !empty($rekap['jam_masuk_kantor'])) {
                        $jam_masuk    = new DateTime($rekap['tanggal_masuk'] . ' ' . $rekap['jam_masuk']);
                        $jam_kantor   = new DateTime($rekap['tanggal_masuk'] . ' ' . $rekap['jam_masuk_kantor']);
                        
                        if ($jam_masuk > $jam_kantor) {
                            $diff_terlambat  = $jam_kantor->diff($jam_masuk);
                            $total_terlambat = $diff_terlambat->h . ' jam ' . $diff_terlambat->i . ' menit';
                        } else {
                            $total_terlambat = '<span class="badge bg-success">On Time</span>';
                        }
                    }
                    ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= esc($rekap['nama']) ?></td>
                <td><?= esc($rekap['nama_shift'] ?? '-') ?></td>
                <td><?= date('d F Y', strtotime($rekap['tanggal_masuk'])) ?></td>
                <td><?= esc($rekap['jam_masuk']) ?></td>
                <td><?= !empty($rekap['jam_keluar']) ? esc($rekap['jam_keluar']) : '-' ?></td>
                <td><?= $total_jam_kerja ?></td>
                <td><?= $total_terlambat ?></td>
            </tr>
            <?php endforeach; ?>
            <?php else : ?>
            <tr>
                <td colspan="8" class="text-center">Tidak ada data presensi</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?= $this->endSection() ?>