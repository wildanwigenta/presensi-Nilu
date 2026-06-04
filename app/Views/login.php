<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="shortcut icon" href="assets/images/favicon.svg" type="image/x-icon" />
    <title>Sign In | Sistem Presensi</title>

    <!-- ========== All CSS files linkup ========= -->
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap.min.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('assets/css/main.css') ?>" />

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #ffffff 0%, #0D2B3E 100%);
            font-family: 'Segoe UI', 'Inter', system-ui, -apple-system, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        #preloader {
            background: #0A1929;
        }

        /* Card bentuk kotak - biru + putih */
        .hotspot-card {
            background: #FFFFFF;
            border-radius: 0px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 380px;
            margin: 1rem;
            border: none;
            overflow: hidden;
        }

        /* Header card warna biru  */
        .card-header-blue {
            background: #2188FF;
            padding: 1.5rem 1.5rem 1.2rem;
            text-align: center;
        }

        .hotspot-title {
            font-size: 1.6rem;
            font-weight: 600;
            color: #FFFFFF;
            margin-bottom: 0.25rem;
            letter-spacing: 0px;
        }

        .hotspot-sub {
            color: rgba(255, 255, 255, 0.85);
            font-size: 0.85rem;
            font-weight: 400;
        }

        /* Body card warna putih */
        .card-body-white {
            background: #FFFFFF;
            padding: 1.8rem 1.8rem 2rem;
        }

        .form-control-custom {
            width: 100%;
            padding: 10px 14px;
            font-size: 0.9rem;
            border: 1.5px solid #E2E8F0;
            border-radius: 0px;
            transition: all 0.2s;
            background: #FFFFFF;
            color: #1a1a2e;
        }

        .form-control-custom:focus {
            border-color: #2188FF;
            outline: none;
            box-shadow: none;
            border-width: 1.5px;
        }

        .btn-connect {
            background: #2188FF;
            border: none;
            padding: 10px;
            font-weight: 600;
            font-size: 0.9rem;
            border-radius: 0px;
            width: 100%;
            color: white;
            transition: all 0.2s;
            margin-top: 0.5rem;
            cursor: pointer;
        }

        .btn-connect:hover {
            background: #1a6fd6;
        }

        .powered {
            text-align: center;
            font-size: 0.65rem;
            color: #8A99AA;
            margin-top: 1.5rem;
            letter-spacing: 0.3px;
        }

        .input-group-custom {
            margin-bottom: 1.2rem;
        }

        .input-group-custom label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: #1E2F3E;
            font-size: 0.8rem;
        }

        .alert-custom {
            background: #FEE2E2;
            border-left: 3px solid #EF4444;
            color: #991B1B;
            padding: 8px 12px;
            border-radius: 0px;
            font-size: 0.8rem;
            margin-bottom: 1rem;
        }

        /* Hilangkan style bawaan section signin */
        .signin-section,
        .auth-row,
        .signin-wrapper {
            background: transparent !important;
            box-shadow: none !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        .container-fluid {
            padding: 0;
        }

        /* Sembunyikan elemen samping kiri */
        .col-lg-6:first-child {
            display: none;
        }

        .row.g-0 {
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        .col-lg-6 {
            flex: 0 0 100%;
            max-width: 100%;
            display: flex;
            justify-content: center;
            padding: 0;
        }

        .signin-wrapper {
            width: 100%;
            display: flex;
            justify-content: center;
            background: transparent !important;
        }

        .form-wrapper {
            background: transparent;
            box-shadow: none;
            padding: 0;
            width: 100%;
            display: flex;
            justify-content: center;
        }

        .invalid-feedback {
            color: #DC2626;
            font-size: 0.7rem;
            margin-top: 4px;
        }

        .is-invalid {
            border-color: #DC2626 !important;
        }

        .alert-custom .btn-close {
            filter: brightness(0.3);
            font-size: 10px;
            padding: 0;
        }
    </style>
</head>

<body>
    <div id="preloader">
        <div class="spinner"></div>
    </div>

    <section class="signin-section">
        <div class="container-fluid">
            <div class="row g-0 auth-row">
                <div class="col-lg-6">
                    <!-- Elemen asli disembunyikan, tetap ada agar struktur tidak error -->
                    <div class="auth-cover-wrapper bg-primary-100">
                        <div class="auth-cover">
                            <div class="title text-center">
                                <h1 class="text-primary mb-10">Welcome Back</h1>
                                <p class="text-medium">Sign in to your account to continue</p>
                            </div>
                            <div class="cover-image">
                                <img src="<?= base_url('assets/images/auth/signin-image.svg')?>" alt="" />
                            </div>
                            <div class="shape-image">
                                <img src="<?= base_url('assets/images/auth/shape.svg')?>" alt="" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="signin-wrapper">
                        <div class="form-wrapper">
                            <!-- KARTU BENTUK KOTAK - BIRU + PUTIH -->
                            <div class="hotspot-card">
                                <!-- Header biru  -->
                                <div class="card-header-blue">
                                    <div class="hotspot-title">Welcome Back</div>
                                    <div class="hotspot-sub">NILU</div>
                                </div>

                                <!-- Body Putih -->
                                <div class="card-body-white">
                                    <?php if (!empty(session()->getFlashdata('pesan'))) : ?>
                                    <div class="alert-custom alert-dismissible fade show" role="alert"
                                        style="display: flex; justify-content: space-between; align-items: center;">
                                        <span><?= session()->getFlashdata('pesan') ?></span>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"
                                            aria-label="Close" style="font-size: 10px;"></button>
                                    </div>
                                    <?php endif ?>

                                    <form method="POST" action="<?= base_url('login')?>">
                                        <?= csrf_field() ?>

                                        <div class="input-group-custom">
                                            <label>Username</label>
                                            <input type="text"
                                                class="form-control-custom <?= (isset($validation) && $validation->hasError('username')) ? 'is-invalid' : '' ?>"
                                                placeholder="nama" name="username" value="<?= old('username') ?>" />
                                            <div class="invalid-feedback">
                                                <?= (isset($validation)) ? $validation->getError('username') : '' ?>
                                            </div>
                                        </div>

                                        <div class="input-group-custom">
                                            <label>Password</label>
                                            <input type="password"
                                                class="form-control-custom <?= (isset($validation) && $validation->hasError('password')) ? 'is-invalid' : '' ?>"
                                                placeholder="Password" name="password" />
                                            <div class="invalid-feedback">
                                                <?= (isset($validation)) ? $validation->getError('password') : '' ?>
                                            </div>
                                        </div>

                                        <button type="submit" class="btn-connect">
                                            Connect
                                        </button>
                                    </form>

                                    <div class="powered">
                                        Powered by MikroTik RouterOS
                                    </div>
                                </div>
                            </div>
                            <!-- END KARTU BIRU PUTIH KOTAK -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="<?= base_url('assets/js/bootstrap.bundle.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/main.js') ?>"></script>
</body>

</html>