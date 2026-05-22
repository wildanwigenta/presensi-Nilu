<?= $this->extend('admin/layout.php') ?>

<?= $this->section('content') ?>


<div class="card col-md-6">
        <div class="card-body ">
            <form method="POST" action="<?= base_url('admin/data_pegawai/store') ?>" enctype="multipart/form-data">

               <?= csrf_field() ?>

            <div class="input-style-1">
            <label>Nama</label>
            <input type="text"  class="form-control <?= ($validation->hasError('nama'))? 'is-invalid' : '' ?>" 
            name="nama" placeholder="Nama" value="<?= set_value('nama') ?>"/>
            <div class="invalid-feedback"><?= $validation->getError('nama') ?></div>
            </div>

            <div class="input-style-1">
            <label>Jenis Kelamin</label>
           <select name="jenis_kelamin" class="form-control <?= ($validation->hasError('jenis_kelamin'))? 'is-invalid' : '' ?>"  >
                <option value="">--Pilih Jenis Kelamin--</option>
                <option value="laki laki">Laki Laki</option>
                <option value="perempuan">Perempuan</option>
           </select>
            <div class="invalid-feedback"><?= $validation->getError('jenis_kelamin') ?></div>
            </div>

            <div class="input-style-1">
            <label>Alamat</label>
            <textarea name="alamat" class="form-control <?= ($validation->hasError('alamat'))? 'is-invalid' : '' ?>" 
            cols="30" rows="5" placeholder="Alamat Lokasi"></textarea>
            <div class="invalid-feedback"><?= $validation->getError('alamat') ?></div>
            </div>

             <div class="input-style-1">
            <label>No HP</label>
            <input type="text"  class="form-control <?= ($validation->hasError('no_hp'))? 'is-invalid' : '' ?>" 
            name="no_hp" placeholder="No HP" />
            <div class="invalid-feedback"><?= $validation->getError('no_hp') ?></div>
            </div>

            <div class="input-style-1">
            <label>Jabatan</label>
           <select name="jabatan" class="form-control <?= ($validation->hasError('jabatan'))? 'is-invalid' : '' ?>"  >
                <option value="">--Pilih Jabatan--</option>
                <?php foreach($jabatan as $jab) : ?>
                        <option value="<?= $jab['jabatan'] ?>"><?= $jab['jabatan'] ?></option>
                        <?php endforeach; ?>
           </select>
            <div class="invalid-feedback"><?= $validation->getError('jabatan') ?></div>
            </div>

             <div class="input-style-1">
            <label>Lokasi Presensi</label>
           <select name="lokasi_presensi" class="form-control <?= ($validation->hasError('lokasi_presensi'))? 'is-invalid' : '' ?>"  >
                <option value="">--Pilih Lokasi Presensi--</option>
                <?php foreach($lokasi_presensi as $lok) : ?>
                        <option value="<?= $lok['id'] ?>"><?= $lok['nama_lokasi'] ?></option>
                        <?php endforeach; ?>
           </select>
            <div class="invalid-feedback"><?= $validation->getError('lokasi_presensi') ?></div>
            </div>

            <div class="input-style-1">
            <label>Shift</label>
            <div class="row">
                <?php if(!empty($shifts)): ?>
                    <?php foreach($shifts as $shift): ?>
                        <div class="col-md-6 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="shift_ids[]" value="<?= $shift['id'] ?>" id="shift_<?= $shift['id'] ?>"
                                    <?= in_array($shift['id'], old('shift_ids', [])) ? 'checked' : '' ?> >
                                <label class="form-check-label" for="shift_<?= $shift['id'] ?>">
                                    <?= $shift['nama_lokasi'] ?> - <?= $shift['nama_shift'] ?> (<?= $shift['jam_masuk'] ?> - <?= $shift['jam_keluar'] ?>)
                                </label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-muted">Belum ada data shift.</div>
                <?php endif; ?>
            </div>
            </div>

            <div class="input-style-1">
            <label>Foto</label>
            <input type="file"  class="form-control <?= ($validation->hasError('foto'))? 'is-invalid' : '' ?>" 
            name="foto" />
            <div class="invalid-feedback"><?= $validation->getError('foto') ?></div>
            </div>

            <div class="input-style-1">
              <label>Registrasi Wajah</label>
              <div id="face_registration_container" class="border rounded p-3 mb-2" style="position: relative; max-width: 340px;">
                <div id="face_status" class="text-muted">Memuat kamera...</div>
                <div id="face_saved_status" class="text-success mt-2"></div>
              </div>
              <input type="hidden" id="face_descriptor" name="face_descriptor" value="" />
              <small class="text-secondary">Webcam akan aktif otomatis, dan sistem akan mendeteksi wajah secara realtime.</small>
            </div>

            <div class="input-style-1">
            <label>Username</label>
            <input type="text"  class="form-control <?= ($validation->hasError('username'))? 'is-invalid' : '' ?>" 
            name="username" placeholder="Username" />
            <div class="invalid-feedback"><?= $validation->getError('username') ?></div>
            </div>

            <div class="input-style-1">
            <label>Password</label>
            <input type="password"  class="form-control <?= ($validation->hasError('password'))? 'is-invalid' : '' ?>" 
            name="password" placeholder="Password" />
            <div class="invalid-feedback"><?= $validation->getError('password') ?></div>
            </div>
            
            <div class="input-style-1">
            <label>Konfirmasi Password</label>
            <input type="password"  class="form-control <?= ($validation->hasError('konfirmasi_password'))? 'is-invalid' : '' ?>" 
            name="konfirmasi_password" placeholder="Konfirmasi Password" />
            <div class="invalid-feedback"><?= $validation->getError('konfirmasi_password') ?></div>
            </div>

            <div class="input-style-1">
            <label>Role</label>
           <select name="role" class="form-control <?= ($validation->hasError('role'))? 'is-invalid' : '' ?>"  >
                <option value="">--Pilih Role--</option>
                <option value="admin">Admin</option>
                <option value="pegawai">Pegawai</option>
           </select>
            <div class="invalid-feedback"><?= $validation->getError('role') ?></div>
            </div>


            <button type="submit" class="btn btn-primary">Simpan</button>
</form>
        </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script src="<?= base_url('assets/js/face-registration.js') ?>"></script>

    
<?= $this->endSection() ?>