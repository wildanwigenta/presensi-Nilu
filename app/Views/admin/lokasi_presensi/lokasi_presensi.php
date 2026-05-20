<?= $this->extend('admin/layout.php') ?>

<?= $this->section('content') ?>

<a href="<?= base_url('admin/lokasi_presensi/create')?>" class="btn btn-primary "><svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M12.0002 4.875C12.6216 4.875 13.1252 5.37868 13.1252 6V10.8752H18.0007C18.622 10.8752 19.1257 11.3789 19.1257 12.0002C19.1257 
12.6216 18.622 13.1252 18.0007 13.1252H13.1252V18.0007C13.1252 18.622 12.6216 19.1257 12.0002 19.1257C11.3789 19.1257 10.8752 18.622 
10.8752 18.0007V13.1252H6C5.37868 13.1252 4.875 12.6216 4.875 12.0002C4.875 11.3789 5.37868 10.8752 6 10.8752H10.8752V6C10.8752 5.37868 
11.3789 4.875 12.0002 4.875Z" fill="#323544"/>
</svg>Tambahkan Data</a>

<table class="table table-striped" id="datatables">
    <thead>
        <tr>
            <th>No</th>
            <th>Nama Lokasi</th>
            <th>Alamat Lokasi</th>
            <th>Tipe Lokasi</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
    <?php $no = 1; foreach($lokasi_presensi as $lok) : ?>
    
        <tr>
            <td><?= $no++ ?></td>
            <td><?= $lok['nama_lokasi']?></td>
            <td><?= $lok['alamat_lokasi']?></td>
            <td><?= $lok['tipe_lokasi']?></td>
            <td>
                <a href="<?= base_url('admin/lokasi_presensi/detail/'. $lok ['id'])?>" class="badge bg-primary">Detail</a>
                <a href="<?= base_url('admin/lokasi_presensi/edit/'. $lok ['id'])?>" class="badge bg-primary">Edit</a>
                <a href="<?= base_url('admin/lokasi_presensi/delete/'. $lok ['id'])?>" class="badge bg-danger tombol-hapus">Hapus</a>
            </td>
        </tr>
    
    <?php endforeach; ?>
    </tbody>
</table>

    
<?= $this->endSection() ?>