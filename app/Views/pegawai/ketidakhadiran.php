<?= $this->extend('pegawai/layout.php') ?>

<?= $this->section('content') ?>

<a href="<?= base_url('pegawai/ketidakhadiran/create')?>" class="btn btn-primary "><svg width="24" height="24"
        viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M12.0002 4.875C12.6216 4.875 13.1252 5.37868 13.1252 6V10.8752H18.0007C18.622 10.8752 19.1257 11.3789 19.1257 12.0002C19.1257 
12.6216 18.622 13.1252 18.0007 13.1252H13.1252V18.0007C13.1252 18.622 12.6216 19.1257 12.0002 19.1257C11.3789 19.1257 10.8752 18.622 
10.8752 18.0007V13.1252H6C5.37868 13.1252 4.875 12.6216 4.875 12.0002C4.875 11.3789 5.37868 10.8752 6 10.8752H10.8752V6C10.8752 5.37868 
11.3789 4.875 12.0002 4.875Z" fill="#323544" />
    </svg>Ajukan</a>


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

    <?php if($ketidakhadiran) : ?>
    <tbody>
        <?php $no = 1;
    foreach($ketidakhadiran as $ketidakhadiran) : ?>

        <tr>
            <td><?= $no++ ?></td>
            <td><?= $ketidakhadiran['tanggal']?></td>
            <td><?= $ketidakhadiran['keterangan']?></td>
            <td><?= $ketidakhadiran['deskripsi']?></td>
            <td>
                <a class="badge bg-primary"
                    href="<?= base_url('file_ketidakhadiran/' . $ketidakhadiran['file'])?>">Dowenload</a>
            </td>
            <td><?= $ketidakhadiran['status']?></td>
            <td>

                <a href="<?= base_url('pegawai/ketidakhadiran/edit/'. $ketidakhadiran ['id'])?>"
                    class="badge bg-primary">Edit</a>
                <a href="<?= base_url('pegawai/ketidakhadiran/delete/'. $ketidakhadiran ['id'])?>"
                    class="badge bg-danger tombol-hapus">Hapus</a>
            </td>
        </tr>

        <?php endforeach; ?>
    </tbody>
    <?php else : ?>

    <tbody>
        <tr>
            <td colspan="7">Data Masih Kosong</td>
        </tr>
    </tbody>

    <?php endif ;?>

</table>

<?= $this->endSection() ?>