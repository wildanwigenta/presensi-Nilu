<?= $this->extend('admin/layout.php') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Shift Lokasi: <?= esc($lokasi['nama_lokasi']) ?></h4>
    <a href="<?= base_url('admin/shifts/create/' . $lokasi['id']) ?>" class="btn btn-primary">Tambah Shift</a>
</div>

<table class="table table-striped" id="datatables">
    <thead>
        <tr>
            <th>No</th>
            <th>Nama Shift</th>
            <th>Jam Masuk</th>
            <th>Jam Keluar</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php $no = 1; foreach($shifts as $shift): ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= esc($shift['nama_shift']) ?></td>
            <td><?= esc($shift['jam_masuk']) ?></td>
            <td><?= esc($shift['jam_keluar']) ?></td>
            <td>
                <a href="<?= base_url('admin/shifts/edit/' . $shift['id']) ?>" class="badge bg-primary">Edit</a>
                <a href="<?= base_url('admin/shifts/delete/' . $shift['id']) ?>" class="badge bg-danger tombol-hapus">Hapus</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?= $this->endSection() ?>