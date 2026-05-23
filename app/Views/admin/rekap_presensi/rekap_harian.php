<?= $this->extend('admin/layout.php') ?>

<?= $this->section('content') ?>

<form class="row g-3" method="GET" action="<?= base_url('admin/rekap_harian') ?>">
    <div class="col-auto">
        <input type="date" class="form-control" name="filter_tanggal" value="<?= $tanggal ?? '' ?>">
    </div>
    <div class="col-auto">
        <button type="submit" name="action" value="filter" class="btn btn-primary mb-3">Tampilkan</button>
    </div>
    <div class="col-auto">
        <button type="submit" name="action" value="excel" class="btn btn-success mb-3">Export Excel</button>
    </div>
</form>

<span>Menampilkan Data :
    <?php if ($tanggal) : ?>
        <?= date('d F Y', strtotime($tanggal)) ?>
    <?php else : ?>
        <?= date('d F Y') ?>
    <?php endif; ?>
</span>

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
            <?php if (!empty($rekap_harian)) : ?>
                <?php $no = 1; foreach ($rekap_harian as $rekap) : ?>
                    <?php
                    // Hitung total jam kerja
                    $total_jam_kerja = '-';
                    if (!empty($rekap['jam_masuk']) && !empty($rekap['jam_keluar']) 
                    && $rekap['jam_keluar'] !== '00:00:00' 
                    && $rekap['tanggal_keluar'] !== '0000-00-00'
                    && $rekap['tanggal_keluar'] !== null) {
                        $masuk  = new DateTime($rekap['tanggal_masuk'] . ' ' . $rekap['jam_masuk']);
                        $keluar = new DateTime(($rekap['tanggal_keluar'] ?: $rekap['tanggal_masuk']) . ' ' . $rekap['jam_keluar']);
                        if ($keluar < $masuk) {
                            $keluar->modify('+1 day');
                        }
                        $diff            = $masuk->diff($keluar);
                        $total_jam_kerja = (($diff->days * 24) + $diff->h) . ' Jam ' . $diff->i . ' Menit';
                    }

                    // Hitung keterlambatan
                    $total_terlambat = '-';
                    if (!empty($rekap['jam_masuk']) && !empty($rekap['jam_masuk_kantor'])) {
                        $masuk  = new DateTime($rekap['tanggal_masuk'] . ' ' . $rekap['jam_masuk']);
                        $kantor = new DateTime($rekap['tanggal_masuk'] . ' ' . $rekap['jam_masuk_kantor']);
                        if ($masuk <= $kantor) {
                            $total_terlambat = '<span class="badge bg-success">On Time</span>';
                        } else {
                            $diff            = $kantor->diff($masuk);
                            $total_terlambat = $diff->h . ' Jam ' . $diff->i . ' Menit';
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