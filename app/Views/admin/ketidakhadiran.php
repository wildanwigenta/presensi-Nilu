<?= $this->extend('admin/layout.php') ?>

<?= $this->section('content') ?>

<table class="table table-striped" id="datatables">
    <thead>
        <tr>
            <th>No</th>
            <th>Tanggal</th>
            <th>Keterangan</th>
            <th>Deskripsi</th>
            <th>File</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($ketidakhadiran) : ?>
        <?php $no = 1; ?>
        <?php foreach ($ketidakhadiran as $item) : ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= $item['tanggal'] ?></td>
            <td><?= $item['keterangan'] ?></td>
            <td><?= $item['deskripsi'] ?></td>
            <td>
                <a class="badge bg-primary" href="<?= base_url('file_ketidakhadiran/' . $item['file']) ?>">
                    Download
                </a>
            </td>
            <td>
                <?php if ($item['status'] == 'Pending') : ?>
                <span class="text-danger"><?= $item['status'] ?></span>
                <?php else : ?>
                <span class="text-success"><?= $item['status'] ?></span>
                <?php endif; ?>
            </td>
            <td>
                <a class="badge bg-success" href="<?= base_url('admin/approved_ketidakhadiran/' . $item['id']) ?>">
                    Approved
                </a>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<?= $this->endSection() ?>