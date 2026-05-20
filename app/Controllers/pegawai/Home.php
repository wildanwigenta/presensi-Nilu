<?php

namespace App\Controllers\Pegawai;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\LokasiPresensiModel;
use App\Models\PegawaiModel;
use App\Models\PresensiModel;
use App\Models\PegawaiShiftModel;


class Home extends BaseController
{
  
    public function index()
    {
        $lokasi_presensi = new LokasiPresensiModel();
        $pegawai_model = new PegawaiModel();
        $presensi_model = new PresensiModel();
        $pegawai_shift_model = new PegawaiShiftModel();
        
        $id_pegawai = session()->get('id_pegawai');
        
        // Pastikan id_pegawai ada di session
        if(!$id_pegawai) {
            session()->setFlashdata('pesan', 'Data pegawai tidak ditemukan');
            return redirect()->to('/logout');
        }
        
        $pegawai = $pegawai_model->where('id', $id_pegawai)->first();
        
        if(!$pegawai) {
            session()->setFlashdata('pesan', 'Data pegawai tidak ditemukan');
            return redirect()->to('/logout');
        }

        $shifts = $pegawai_shift_model
            ->select('shifts.*')
            ->join('shifts', 'shifts.id = pegawai_shift.shift_id')
            ->where('pegawai_shift.pegawai_id', $id_pegawai)
            ->findAll();
        
        $data = [
            'title' => 'Home', 
            'lokasi_presensi' => $lokasi_presensi->where('id', $pegawai['lokasi_presensi'])->first(),
            'cek_presensi' => $presensi_model->where('id_pegawai', $id_pegawai)->where('tanggal_masuk', date('Y-m-d'))->countAllResults(),
            'cek_presensi_keluar' => $presensi_model->where('id_pegawai', $id_pegawai)->where('tanggal_masuk', date('Y-m-d'))->where('tanggal_keluar IS NOT NULL')->countAllResults(),
            'ambil_presensi_masuk' => $presensi_model->where('id_pegawai', $id_pegawai)->where('tanggal_masuk', date('Y-m-d'))->first(),
            'pegawai' => $pegawai,
            'shifts' => $shifts
        ];

        return view('pegawai/home', $data);
    }

    public function presensi_masuk()
    {
        $latitude_pegawai = (float) $this->request->getPost('latitude_pegawai');
        $latitude_outlet = (float) $this->request->getPost('latitude_outlet');
        $radius = $this->request->getPost('radius');

        $jarak = sin(deg2rad($latitude_pegawai)) * sin(deg2rad($latitude_outlet)) + 
        cos(deg2rad($latitude_pegawai)) * cos(deg2rad($latitude_outlet));
        $jarak = acos($jarak);
        $jarak = rad2deg($jarak);
        $mil = $jarak * 60 * 1.1515;
        $km = $mil * 1.609344;
        $jarak_meter = floor ($km * 1000);
        
        if($jarak_meter > $radius){
        session()->setFlashdata('gagal', 'Presensi anda gagal, anda berada diluar radius outlet');
        return redirect()->to(base_url('pegawai/home'));
    } else{
        $data = [
    'title' => "Ambil Foto Selfie",
    'id_pegawai' => $this->request->getPost('id_pegawai'),
    'tanggal_masuk' => $this->request->getPost('tanggal_masuk'),
    'jam_masuk' => $this->request->getPost('jam_masuk'),
    'shift_id' => $this->request->getPost('shift_id'),
];

        return view('pegawai/ambil_foto', $data);
    }
    }
    public function presensi_masuk_aksi()
    {
        $request = \Config\Services::request();
        $id_pegawai = $request->getPost('id_pegawai');
        $tanggal_masuk = $request->getPost('tanggal_masuk');
        $jam_masuk = $request->getPost('jam_masuk');
        $shift_id = $request->getPost('shift_id');
        $foto_masuk = $request->getPost('foto_masuk');

        $foto_masuk = str_replace('data:image/jpeg;base64,', '', $foto_masuk );
        $foto_masuk = base64_decode($foto_masuk);

        $foto_dir = 'uploads/'.$id_pegawai . '_' . time().'.jpg';
        $nama_foto = $id_pegawai . '_' . time() . '.jpg';
        file_put_contents($foto_dir, $foto_masuk);

        $presensi_model = new PresensiModel();
        $presensi_model->insert([
            'id_pegawai' =>$id_pegawai,
            'tanggal_masuk' =>$tanggal_masuk,
            'jam_masuk' =>$jam_masuk,
            'shift_id' => $shift_id,
            'foto_masuk' => $nama_foto
           ]);
        session()->setFlashData('berhasil', 'Presensi Masuk Berhasil');
         return redirect()->to(base_url('pegawai/home')) ;

    }

    public function presensi_keluar($id)
    {
        $latitude_pegawai = (float) $this->request->getPost('latitude_pegawai');
        $latitude_outlet = (float) $this->request->getPost('latitude_outlet');
        $radius = $this->request->getPost('radius');

        $jarak = sin(deg2rad($latitude_pegawai)) * sin(deg2rad($latitude_outlet)) + 
        cos(deg2rad($latitude_pegawai)) * cos(deg2rad($latitude_outlet));
        $jarak = acos($jarak);
        $jarak = rad2deg($jarak);
        $mil = $jarak * 60 * 1.1515;
        $km = $mil * 1.609344;
        $jarak_meter = floor ($km * 1000);

        
        if($jarak_meter > $radius){
        session()->setFlashdata('gagal', 'Presensi anda gagal, anda berada diluar radius outlet');
        return redirect()->to(base_url('pegawai/home'));
    } else{
        $data = [
    'title' => "Ambil Foto Selfie",
    'id_presensi' => $id,
    'tanggal_keluar' => $this->request->getPost('tanggal_keluar'),
    'jam_keluar' => $this->request->getPost('jam_keluar'),
];

        return view('pegawai/ambil_foto_keluar', $data);
    }
    }

    public function presensi_keluar_aksi($id)
    {
        $request = \Config\Services::request();
        $tanggal_keluar = $request->getPost('tanggal_keluar');
        $jam_keluar = $request->getPost('jam_keluar');
        $foto_keluar = $request->getPost('foto_keluar');

        $foto_keluar = str_replace('data:image/jpeg;base64,', '', $foto_keluar );
        $foto_keluar = base64_decode($foto_keluar);

        $foto_dir = 'uploads/'.$id . '_' . time().'.jpg';
        $nama_foto = $id . '_' . time() . '.jpg';
        file_put_contents($foto_dir, $foto_keluar);

        $presensi_model = new PresensiModel();
        $presensi_model->update($id,[
            'tanggal_keluar' =>$tanggal_keluar,
            'jam_keluar' =>$jam_keluar,
            'foto_keluar' => $nama_foto
           ]);
        session()->setFlashData('berhasil', 'Presensi Keluar Berhasil');
         return redirect()->to(base_url('pegawai/home')) ;

    }

}
