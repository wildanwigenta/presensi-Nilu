<?php

namespace App\Controllers\Pegawai;

use App\Controllers\BaseController;
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

        $today = date('Y-m-d');
        $today_presensi = $presensi_model
            ->where('id_pegawai', $id_pegawai)
            ->where('tanggal_masuk', $today)
            ->countAllResults();

        $open_presensi = $presensi_model
            ->where('id_pegawai', $id_pegawai)
            ->where('tanggal_masuk', $today)
            ->where('tanggal_keluar IS NULL', null, false)
            ->countAllResults();

        $ambil_presensi_masuk = $presensi_model
            ->where('id_pegawai', $id_pegawai)
            ->where('tanggal_masuk', $today)
            ->where('tanggal_keluar IS NULL', null, false)
            ->orderBy('id', 'DESC')
            ->first();

        $data = [
            'title' => 'Home', 
            'lokasi_presensi' => $lokasi_presensi->where('id', $pegawai['lokasi_presensi'])->first(),
            'cek_presensi' => $today_presensi,
            'open_presensi' => $open_presensi,
            'cek_presensi_keluar' => $today_presensi - $open_presensi,
            'ambil_presensi_masuk' => $ambil_presensi_masuk,
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
        $shift_id = $this->request->getPost('shift_id');
        $tanggal_masuk = $this->request->getPost('tanggal_masuk');

        $id_pegawai = session()->get('id_pegawai');
        if (!$id_pegawai) {
            session()->setFlashdata('gagal', 'Sesi pegawai tidak valid.');
            return redirect()->to(base_url('pegawai/home'));
        }

        if (empty($shift_id) || !is_numeric($shift_id)) {
            session()->setFlashdata('gagal', 'Pilih shift terlebih dahulu.');
            return redirect()->to(base_url('pegawai/home'));
        }

        if ((int) $this->request->getPost('id_pegawai') !== (int) $id_pegawai) {
            session()->setFlashdata('gagal', 'Data pegawai tidak sesuai.');
            return redirect()->to(base_url('pegawai/home'));
        }

        $pegawaiShiftModel = new PegawaiShiftModel();
        $assignedShift = $pegawaiShiftModel
            ->where('pegawai_id', $id_pegawai)
            ->where('shift_id', (int) $shift_id)
            ->countAllResults();

        if ($assignedShift < 1) {
            session()->setFlashdata('gagal', 'Shift yang dipilih tidak terdaftar untuk Anda.');
            return redirect()->to(base_url('pegawai/home'));
        }

        $presensi_model = new PresensiModel();
        $existing = $presensi_model
            ->where('id_pegawai', $id_pegawai)
            ->where('tanggal_masuk', $tanggal_masuk)
            ->where('shift_id', $shift_id)
            ->countAllResults();

        if ($existing > 0) {
            session()->setFlashdata('gagal', 'Anda telah melakukan presensi untuk shift yang sama hari ini');
            return redirect()->to(base_url('pegawai/home'));
        }

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
    }


        $data = [
            'title' => "Ambil Foto Selfie",
            'id_pegawai' => $id_pegawai,
            'tanggal_masuk' => $this->request->getPost('tanggal_masuk'),
            'jam_masuk' => $this->request->getPost('jam_masuk'),
            'shift_id' => $shift_id,
        ];

        return view('pegawai/ambil_foto', $data);
    }
    public function presensi_masuk_aksi()
    {
        $request = \Config\Services::request();
        $id_pegawai = session()->get('id_pegawai');
        if (!$id_pegawai) {
            session()->setFlashdata('gagal', 'Sesi pegawai tidak valid.');
            return redirect()->to(base_url('pegawai/home'));
        }
        $tanggal_masuk = $request->getPost('tanggal_masuk');
        $jam_masuk = $request->getPost('jam_masuk');
        $shift_id = $request->getPost('shift_id');
        $foto_masuk = $request->getPost('foto_masuk');

        if (empty($shift_id) || !is_numeric($shift_id)) {
            session()->setFlashdata('gagal', 'Shift tidak valid.');
            return redirect()->to(base_url('pegawai/home'));
        }

        $pegawaiShiftModel = new PegawaiShiftModel();
        $assignedShift = $pegawaiShiftModel
            ->where('pegawai_id', $id_pegawai)
            ->where('shift_id', (int) $shift_id)
            ->countAllResults();

        if ($assignedShift < 1) {
            session()->setFlashdata('gagal', 'Shift yang dipilih tidak terdaftar untuk Anda.');
            return redirect()->to(base_url('pegawai/home'));
        }

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
        $id_pegawai = session()->get('id_pegawai');
        $presensi_model = new PresensiModel();

        $presensi = $presensi_model
            ->where('id', $id)
            ->where('id_pegawai', $id_pegawai)
            ->where('tanggal_keluar IS NULL', null, false)
            ->first();

        if(!$presensi){
            session()->setFlashdata('gagal', 'Data presensi tidak valid atau sudah keluar.');
            return redirect()->to(base_url('pegawai/home'));
        }

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
        }

        $data = [
            'title' => "Ambil Foto Selfie",
            'id_presensi' => $id,
            'tanggal_keluar' => $this->request->getPost('tanggal_keluar'),
            'jam_keluar' => $this->request->getPost('jam_keluar'),
        ];

        return view('pegawai/ambil_foto_keluar', $data);
    }

    public function presensi_keluar_aksi($id)
    {
        $id_pegawai = session()->get('id_pegawai');
        if (!$id_pegawai) {
            session()->setFlashdata('gagal', 'Sesi pegawai tidak valid.');
            return redirect()->to(base_url('pegawai/home'));
        }

        $request = \Config\Services::request();
        $tanggal_keluar = $request->getPost('tanggal_keluar');
        $jam_keluar = $request->getPost('jam_keluar');
        $foto_keluar = $request->getPost('foto_keluar');

        $presensi_model = new PresensiModel();
        $presensi = $presensi_model
            ->where('id', $id)
            ->where('id_pegawai', $id_pegawai)
            ->where('tanggal_keluar IS NULL', null, false)
            ->first();

        if(!$presensi){
            session()->setFlashdata('gagal', 'Data presensi tidak valid atau sudah keluar.');
            return redirect()->to(base_url('pegawai/home'));
        }

        $foto_keluar = str_replace('data:image/jpeg;base64,', '', $foto_keluar );
        $foto_keluar = base64_decode($foto_keluar);

        $foto_dir = 'uploads/'.$id . '_' . time().'.jpg';
        $nama_foto = $id . '_' . time() . '.jpg';
        file_put_contents($foto_dir, $foto_keluar);

        $presensi_model->update($id,[
            'tanggal_keluar' =>$tanggal_keluar,
            'jam_keluar' =>$jam_keluar,
            'foto_keluar' => $nama_foto
        ]);
        session()->setFlashData('berhasil', 'Presensi Keluar Berhasil');
         return redirect()->to(base_url('pegawai/home')) ;

    }

    public function verify_face()
    {
        $id_pegawai = session()->get('id_pegawai');
        $request = \Config\Services::request();
        if (!$id_pegawai) {
            return $this->response->setJSON([
                'verified' => false,
                'distance' => null,
                'message' => 'Sesi pegawai tidak valid.'
            ]);
        }

        $input = $request->getJSON(true);
        $descriptor = $input['descriptor'] ?? null;

        if (!is_array($descriptor) || empty($descriptor)) {
            return $this->response->setJSON([
                'verified' => false,
                'distance' => null,
                'message' => 'Descriptor wajah tidak valid.'
            ]);
        }

        $pegawaiModel = new PegawaiModel();
        $pegawai = $pegawaiModel->find($id_pegawai);
        $storedDescriptorJson = $pegawai['face_descriptor'] ?? null;

        if (empty($storedDescriptorJson)) {
            return $this->response->setJSON([
                'verified' => false,
                'distance' => null,
                'message' => 'Data descriptor wajah belum disimpan.'
            ]);
        }

        $storedDescriptor = json_decode($storedDescriptorJson, true);
        if (!is_array($storedDescriptor) || count($storedDescriptor) !== count($descriptor)) {
            return $this->response->setJSON([
                'verified' => false,
                'distance' => null,
                'message' => 'Descriptor wajah tidak cocok dengan format.'
            ]);
        }

        $distance = $this->calculateEuclideanDistance($storedDescriptor, $descriptor);
        $threshold = 0.55;
        $verified = $distance !== null && $distance <= $threshold;

        return $this->response->setJSON([
            'verified' => $verified,
            'distance' => $distance,
            'threshold' => $threshold,
            'message' => $verified ? 'Wajah sesuai akun' : 'Wajah tidak sesuai akun'
        ]);
    }

    private function calculateEuclideanDistance(array $a, array $b)
    {
        if (count($a) !== count($b)) {
            return null;
        }

        $sum = 0.0;
        foreach ($a as $index => $value) {
            $diff = (float) $value - (float) $b[$index];
            $sum += $diff * $diff;
        }

        return sqrt($sum);
    }

}
