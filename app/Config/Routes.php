<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'login::index');
$routes->post('login', 'login::login_action');
$routes->get('logout', 'login::logout');

$routes->get('admin/home', 'admin\home::index', ['filter' => 'adminFilter']);
$routes->get('admin/jabatan', 'admin\jabatan::index', ['filter' => 'adminFilter']);
$routes->get('admin/jabatan/create', 'admin\jabatan::create', ['filter' => 'adminFilter']);
$routes->post('admin/jabatan/store', 'admin\jabatan::store', ['filter' => 'adminFilter']);
$routes->get('admin/jabatan/edit/(:segment)', 'admin\jabatan::edit/$1', ['filter' => 'adminFilter']);
$routes->post('admin/jabatan/update/(:segment)', 'admin\jabatan::update/$1', ['filter' => 'adminFilter']);
$routes->get('admin/jabatan/delete/(:segment)', 'admin\jabatan::delete/$1', ['filter' => 'adminFilter']);


$routes->get('admin/lokasi_presensi', 'admin\LokasiPresensi::index', ['filter' => 'adminFilter']);
$routes->get('admin/lokasi_presensi/create', 'admin\LokasiPresensi::create', ['filter' => 'adminFilter']);
$routes->post('admin/lokasi_presensi/store', 'admin\LokasiPresensi::store', ['filter' => 'adminFilter']);
$routes->get('admin/lokasi_presensi/edit/(:segment)', 'admin\LokasiPresensi::edit/$1', ['filter' => 'adminFilter']);
$routes->post('admin/lokasi_presensi/update/(:segment)', 'admin\LokasiPresensi::update/$1', ['filter' => 'adminFilter']);
$routes->get('admin/lokasi_presensi/delete/(:segment)', 'admin\LokasiPresensi::delete/$1', ['filter' => 'adminFilter']);
$routes->get('admin/lokasi_presensi/detail/(:segment)', 'admin\LokasiPresensi::detail/$1', ['filter' => 'adminFilter']);

$routes->get('admin/shifts', 'admin\Shifts::index', ['filter' => 'adminFilter']);
$routes->get('admin/shifts/location/(:segment)', 'admin\Shifts::location/$1', ['filter' => 'adminFilter']);
$routes->get('admin/shifts/create', 'admin\Shifts::create', ['filter' => 'adminFilter']);
$routes->get('admin/shifts/create/(:segment)', 'admin\Shifts::create/$1', ['filter' => 'adminFilter']);
$routes->post('admin/shifts/store', 'admin\Shifts::store', ['filter' => 'adminFilter']);
$routes->get('admin/shifts/edit/(:segment)', 'admin\Shifts::edit/$1', ['filter' => 'adminFilter']);
$routes->post('admin/shifts/update/(:segment)', 'admin\Shifts::update/$1', ['filter' => 'adminFilter']);
$routes->get('admin/shifts/delete/(:segment)', 'admin\Shifts::delete/$1', ['filter' => 'adminFilter']);

$routes->get('admin/data_pegawai', 'admin\DataPegawai::index', ['filter' => 'adminFilter']);
$routes->get('admin/data_pegawai/create', 'admin\DataPegawai::create', ['filter' => 'adminFilter']);
$routes->post('admin/data_pegawai/store', 'admin\DataPegawai::store', ['filter' => 'adminFilter']);
$routes->get('admin/data_pegawai/edit/(:segment)', 'admin\DataPegawai::edit/$1', ['filter' => 'adminFilter']);
$routes->post('admin/data_pegawai/update/(:segment)', 'admin\DataPegawai::update/$1', ['filter' => 'adminFilter']);
$routes->get('admin/data_pegawai/delete/(:segment)', 'admin\DataPegawai::delete/$1', ['filter' => 'adminFilter']);
$routes->get('admin/data_pegawai/detail/(:segment)', 'admin\DataPegawai::detail/$1', ['filter' => 'adminFilter']);

$routes->get('admin/rekap_harian', 'admin\RekapPresensi::rekap_harian', ['filter' => 'adminFilter']);
$routes->get('admin/rekap_bulanan', 'admin\RekapPresensi::rekap_bulanan', ['filter' => 'adminFilter']);

$routes->get('admin/ketidakhadiran', 'admin\Ketidakhadiran::index', ['filter' => 'adminFilter']);
$routes->get('admin/approved_ketidakhadiran/(:segment)', 'admin\Ketidakhadiran::approved/$1', ['filter' => 'adminFilter']);

$routes->get('pegawai/home', 'pegawai\home::index', ['filter' => 'pegawaiFilter']);
$routes->post('pegawai/presensi_masuk', 'pegawai\home::presensi_masuk', ['filter' => 'pegawaiFilter']);
$routes->post('pegawai/presensi_masuk_aksi', 'pegawai\home::presensi_masuk_aksi', ['filter' => 'pegawaiFilter']);

$routes->post('pegawai/presensi_keluar/(:segment)', 'pegawai\home::presensi_keluar/$1', ['filter' => 'pegawaiFilter']);
$routes->post('pegawai/presensi_keluar_aksi/(:segment)', 'pegawai\home::presensi_keluar_aksi/$1', ['filter' => 'pegawaiFilter']);

$routes->get('pegawai/rekap_presensi', 'pegawai\RekapPresensi::index', ['filter' => 'pegawaiFilter']);

$routes->get('pegawai/ketidakhadiran', 'pegawai\Ketidakhadiran::index', ['filter' => 'pegawaiFilter']);
$routes->get('pegawai/ketidakhadiran/create', 'pegawai\Ketidakhadiran::create', ['filter' => 'pegawaiFilter']);
$routes->post('pegawai/ketidakhadiran/store', 'pegawai\Ketidakhadiran::store', ['filter' => 'pegawaiFilter']);
$routes->get('pegawai/ketidakhadiran/edit/(:segment)', 'pegawai\Ketidakhadiran::edit/$1', ['filter' => 'pegawaiFilter']);
$routes->post('pegawai/ketidakhadiran/update/(:segment)', 'pegawai\Ketidakhadiran::update/$1', ['filter' => 'pegawaiFilter']);
$routes->get('pegawai/ketidakhadiran/delete/(:segment)', 'pegawai\Ketidakhadiran::delete/$1', ['filter' => 'pegawaiFilter']);
$routes->get('pegawai/ketidakhadiran/detail/(:segment)', 'pegawai\Ketidakhadiran::detail/$1', ['filter' => 'pegawaiFilter']);
