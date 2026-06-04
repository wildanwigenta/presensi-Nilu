<?= $this->extend('admin/layout.php') ?>

<?= $this->section('content') ?>

<div class="card col-md-6">
    <div class="card-body">
        <form method="POST" action="<?= base_url('admin/shifts/update/' . $shift['id']) ?>">
            <?= csrf_field() ?>

            <div class="input-style-1 mb-3">
                <label>Lokasi Presensi</label>
                <select name="lokasi_presensi_id"
                    class="form-control <?= ($validation->hasError('lokasi_presensi_id')) ? 'is-invalid' : '' ?>">
                    <option value="">-- Pilih Lokasi --</option>
                    <?php foreach($lokasi_presensi as $lokasi): ?>
                    <option value="<?= $lokasi['id'] ?>"
                        <?= old('lokasi_presensi_id', $shift['lokasi_presensi_id']) == $lokasi['id'] ? 'selected' : '' ?>>
                        <?= esc($lokasi['nama_lokasi']) ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback"><?= $validation->getError('lokasi_presensi_id') ?></div>
            </div>

            <div class="input-style-1 mb-3">
                <label>Nama Shift</label>
                <input type="text" name="nama_shift"
                    class="form-control <?= ($validation->hasError('nama_shift')) ? 'is-invalid' : '' ?>"
                    value="<?= old('nama_shift', $shift['nama_shift']) ?>" placeholder="Nama Shift" />
                <div class="invalid-feedback"><?= $validation->getError('nama_shift') ?></div>
            </div>

            <div class="input-style-1 mb-3">
                <label>Jam Masuk</label>
                <input type="time" name="jam_masuk"
                    class="form-control <?= ($validation->hasError('jam_masuk')) ? 'is-invalid' : '' ?>"
                    value="<?= old('jam_masuk', $shift['jam_masuk']) ?>" />
                <div class="invalid-feedback"><?= $validation->getError('jam_masuk') ?></div>
            </div>

            <div class="input-style-1 mb-3">
                <label>Jam Keluar</label>
                <input type="time" name="jam_keluar"
                    class="form-control <?= ($validation->hasError('jam_keluar')) ? 'is-invalid' : '' ?>"
                    value="<?= old('jam_keluar', $shift['jam_keluar']) ?>" />
                <div class="invalid-feedback"><?= $validation->getError('jam_keluar') ?></div>
            </div>

            <button type="submit" class="btn btn-primary">Simpan</button>
        </form>
    </div>
</div>

<?= $this->endSection() ?>