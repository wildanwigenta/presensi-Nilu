<?= $this->extend('admin/layout.php') ?>

<?= $this->section('content') ?>

<div class="card col-md-6">
        <div class="card-body ">
                <form method="POST" action="<?= base_url('admin/data_pegawai/update/'.$pegawai['id']) ?>"
                        enctype="multipart/form-data">
                        <div class="input-style-1">
                                <label>Nama</label>
                                <input type="text"
                                        class="form-control <?= ($validation->hasError('nama'))? 'is-invalid' : '' ?>"
                                        name="nama" placeholder="Nama" value="<?= $pegawai['nama'] ?>" />
                                <div class="invalid-feedback"><?= $validation->getError('nama') ?></div>
                        </div>

                        <div class="input-style-1">
                                <label>Jenis Kelamin</label>
                                <select name="jenis_kelamin"
                                        class="form-control <?= ($validation->hasError('jenis_kelamin'))? 'is-invalid' : '' ?>">
                                        <option value="">--Pilih Jenis Kelamin--</option>
                                        <option <?php if ($pegawai['jenis_kelamin']=='laki laki') {
                                        echo 'selected';
                                        } ?> value="laki laki">Laki Laki</option>
                                        <option <?php if ($pegawai['jenis_kelamin']=='perempuan') {
                                        echo 'selected';
                                        } ?> value="perempuan">Perempuan</option>
                                </select>
                                <div class="invalid-feedback"><?= $validation->getError('jenis_kelamin') ?></div>
                        </div>

                        <div class="input-style-1">
                                <label>Alamat</label>
                                <textarea name="alamat"
                                        class="form-control <?= ($validation->hasError('alamat'))? 'is-invalid' : '' ?>"
                                        cols="30" rows="5"
                                        placeholder="Alamat Lokasi"><?= $pegawai['alamat'] ?></textarea>
                                <div class="invalid-feedback"><?= $validation->getError('alamat') ?></div>
                        </div>

                        <div class="input-style-1">
                                <label>No HP</label>
                                <input type="text"
                                        class="form-control <?= ($validation->hasError('no_hp'))? 'is-invalid' : '' ?>"
                                        name="no_hp" placeholder="No HP" value="<?= $pegawai['no_hp'] ?>" />
                                <div class="invalid-feedback"><?= $validation->getError('no_hp') ?></div>
                        </div>

                        <div class="input-style-1">
                                <label>Jabatan</label>
                                <select name="jabatan"
                                        class="form-control <?= ($validation->hasError('jabatan'))? 'is-invalid' : '' ?>">
                                        <option value="<?= $pegawai['jabatan'] ?>"><?= $pegawai['jabatan'] ?></option>
                                        <?php foreach($jabatan as $jab) : ?>
                                        <option value="<?= $jab['jabatan'] ?>"><?= $jab['jabatan'] ?></option>
                                        <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback"><?= $validation->getError('jabatan') ?></div>
                        </div>

                        <div class="input-style-1">
                                <label>Lokasi Presensi</label>
                                <select name="lokasi_presensi"
                                        class="form-control <?= ($validation->hasError('lokasi_presensi'))? 'is-invalid' : '' ?>">
                                        <option value="<?= $pegawai['lokasi_presensi'] ?>">
                                                <?= $pegawai['lokasi_presensi'] ?></option>
                                        <?php foreach($lokasi_presensi as $lok) : ?>
                                        <option value="<?= $lok['id'] ?>"><?= $lok['nama_lokasi'] ?></option>
                                        <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback"><?= $validation->getError('lokasi_presensi') ?></div>
                        </div>

                        <div class="input-style-1">
                                <label>Shift</label>
                                <div class="shift-grid mt-2">
                                        <?php if(!empty($shifts)): ?>
                                        <?php foreach($shifts as $shift): ?>
                                        <label class="shift-card">
                                                <input class="shift-checkbox" type="checkbox" name="shift_ids[]"
                                                        value="<?= $shift['id'] ?>" id="shift_<?= $shift['id'] ?>"
                                                        <?= in_array($shift['id'], old('shift_ids', $assigned_shift_ids)) ? 'checked' : '' ?>>
                                                <div class="shift-info">
                                                        <span class="shift-name"><?= $shift['nama_shift'] ?></span>
                                                        <span class="shift-meta"><?= $shift['nama_lokasi'] ?></span>
                                                        <span class="shift-badge">
                                                                <i class="ti ti-clock"></i>
                                                                <?= $shift['jam_masuk'] ?> – <?= $shift['jam_keluar'] ?>
                                                        </span>
                                                </div>
                                        </label>
                                        <?php endforeach; ?>
                                        <?php else: ?>
                                        <p class="text-muted small">Belum ada data shift.</p>
                                        <?php endif; ?>
                                </div>
                        </div>

                        <div class="input-style-1">
                                <label>Foto</label>
                                <input type="hidden" value="<?= $pegawai['foto'] ?>" name="foto_lama">
                                <input type="file"
                                        class="form-control <?= ($validation->hasError('foto'))? 'is-invalid' : '' ?>"
                                        name="foto" />
                                <div class="invalid-feedback"><?= $validation->getError('foto') ?></div>
                        </div>

                        <div class="input-style-1">
                          <label>Registrasi Wajah</label>
                          <div id="face_registration_container" class="border rounded p-3 mb-2" style="position: relative; max-width: 340px;">
                            <div id="face_status" class="text-muted">Memuat kamera...</div>
                            <div id="face_saved_status" class="text-success mt-2"><?= !empty($pegawai['face_descriptor']) ? 'Face descriptor sudah ada.' : '' ?></div>
                          </div>
                          <input type="hidden" id="face_descriptor" name="face_descriptor" value="" />
                          <small class="text-secondary">Webcam akan aktif otomatis, dan sistem akan mendeteksi wajah secara realtime.</small>
                        </div>

                        <div class="input-style-1">
                                <label>Username</label>
                                <input type="text"
                                        class="form-control <?= ($validation->hasError('username'))? 'is-invalid' : '' ?>"
                                        name="username" placeholder="Username" value="<?= $pegawai['username'] ?>" />
                                <div class="invalid-feedback"><?= $validation->getError('username') ?></div>
                        </div>

                        <div class="input-style-1">
                                <label>Password</label>
                                <input type="hidden" value="<?= $pegawai['password'] ?>" name="password_lama">
                                <input type="password"
                                        class="form-control <?= ($validation->hasError('password'))? 'is-invalid' : '' ?>"
                                        name="password" placeholder="Password" />
                                <div class="invalid-feedback"><?= $validation->getError('password') ?></div>
                        </div>

                        <div class="input-style-1">
                                <label>Konfirmasi Password</label>
                                <input type="password"
                                        class="form-control <?= ($validation->hasError('konfirmasi_password'))? 'is-invalid' : '' ?>"
                                        name="konfirmasi_password" placeholder="Konfirmasi Password" />
                                <div class="invalid-feedback"><?= $validation->getError('konfirmasi_password') ?></div>
                        </div>

                        <div class="input-style-1">
                                <label>Role</label>
                                <select name="role"
                                        class="form-control <?= ($validation->hasError('role'))? 'is-invalid' : '' ?>">
                                        <option value="">--Pilih Role--</option>
                                        <option <?php if ($pegawai['role']=='admin') {
                                                echo 'selected';
                                        } ?> value="admin">Admin</option>
                                        <option <?php if ($pegawai['role']=='pegawai') {
                                        echo 'selected';
                                        } ?> value="pegawai">Pegawai</option>
                                </select>
                                <div class="invalid-feedback"><?= $validation->getError('role') ?></div>
                        </div>


                        <button type="submit" class="btn btn-primary">Update</button>
                </form>
        </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script src="<?= base_url('assets/js/face-registration.js') ?>"></script>

<?= $this->endSection() ?>
