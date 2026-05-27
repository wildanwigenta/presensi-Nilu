<?= $this->extend('admin/layout.php') ?>

<?= $this->section('content') ?>

<div class="card col-md-6">

        <div class="card-body ">
                <form method="POST" action="<?= base_url('admin/jabatan/store') ?>">

                        <?= csrf_field() ?>

                        <div class="input-style-1">
                                <label>Nama Jabatan</label>
                                <input type="text"
                                        class="form-control <?= ($validation->hasError('jabatan'))? 'is-invalid' : '' ?>"
                                        name="jabatan" placeholder="Nama Jabatan" />
                                <div class="invalid-feedback"><?= $validation->getError('jabatan') ?></div>
                        </div>

                        <button type="submit" class="btn btn-primary">Simpan</button>
                </form>
        </div>
</div>


<?= $this->endSection() ?>