<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\LokasiPresensiModel;
use App\Models\ShiftModel;

class Shifts extends BaseController
{
    public function index()
    {
        $shiftModel = new ShiftModel();

        $shifts = $shiftModel
            ->select('shifts.*, lokasi_presensi.nama_lokasi')
            ->join('lokasi_presensi', 'lokasi_presensi.id = shifts.lokasi_presensi_id')
            ->findAll();

        $data = [
            'title' => 'Data Shift',
            'shifts' => $shifts,
        ];

        return view('admin/shifts/index', $data);
    }

    public function location($lokasiId)
    {
        $lokasiPresensiModel = new LokasiPresensiModel();
        $shiftModel = new ShiftModel();

        $lokasi = $lokasiPresensiModel->find($lokasiId);
        if (!$lokasi) {
            session()->setFlashdata('gagal', 'Lokasi presensi tidak ditemukan');
            return redirect()->to(base_url('admin/shifts'));
        }

        $shifts = $shiftModel
            ->select('shifts.*, lokasi_presensi.nama_lokasi')
            ->join('lokasi_presensi', 'lokasi_presensi.id = shifts.lokasi_presensi_id')
            ->where('shifts.lokasi_presensi_id', $lokasiId)
            ->findAll();

        $data = [
            'title' => 'Shift Lokasi',
            'shifts' => $shifts,
            'lokasi' => $lokasi,
        ];

        return view('admin/shifts/location', $data);
    }

    public function create($lokasiId = null)
    {
        $lokasiPresensiModel = new LokasiPresensiModel();

        $data = [
            'title' => 'Tambah Shift',
            'lokasi_presensi' => $lokasiPresensiModel->findAll(),
            'validation' => \Config\Services::validation(),
            'selected_lokasi' => $lokasiId,
        ];

        return view('admin/shifts/create', $data);
    }

    public function store()
    {
        $rules = [
            'lokasi_presensi_id' => [
                'rules' => 'required',
                'errors' => ['required' => 'Lokasi Presensi wajib dipilih'],
            ],
            'nama_shift' => [
                'rules' => 'required',
                'errors' => ['required' => 'Nama shift wajib diisi'],
            ],
            'jam_masuk' => [
                'rules' => 'required',
                'errors' => ['required' => 'Jam masuk wajib diisi'],
            ],
            'jam_keluar' => [
                'rules' => 'required',
                'errors' => ['required' => 'Jam keluar wajib diisi'],
            ],
        ];

        if (!$this->validate($rules)) {
            $lokasiPresensiModel = new LokasiPresensiModel();
            $data = [
                'title' => 'Tambah Shift',
                'lokasi_presensi' => $lokasiPresensiModel->findAll(),
                'validation' => \Config\Services::validation(),
                'selected_lokasi' => $this->request->getPost('lokasi_presensi_id'),
            ];
            return view('admin/shifts/create', $data);
        }

        $shiftModel = new ShiftModel();
        $shiftModel->insert([
            'lokasi_presensi_id' => $this->request->getPost('lokasi_presensi_id'),
            'nama_shift' => $this->request->getPost('nama_shift'),
            'jam_masuk' => $this->request->getPost('jam_masuk'),
            'jam_keluar' => $this->request->getPost('jam_keluar'),
        ]);

        session()->setFlashData('berhasil', 'Shift berhasil ditambahkan');
        return redirect()->to(base_url('admin/shifts'));
    }

    public function edit($id)
    {
        $shiftModel = new ShiftModel();
        $shift = $shiftModel->find($id);

        if (!$shift) {
            session()->setFlashdata('gagal', 'Shift tidak ditemukan');
            return redirect()->to(base_url('admin/shifts'));
        }

        $lokasiPresensiModel = new LokasiPresensiModel();
        $data = [
            'title' => 'Edit Shift',
            'shift' => $shift,
            'lokasi_presensi' => $lokasiPresensiModel->findAll(),
            'validation' => \Config\Services::validation(),
        ];

        return view('admin/shifts/edit', $data);
    }

    public function update($id)
    {
        $shiftModel = new ShiftModel();
        $shift = $shiftModel->find($id);

        if (!$shift) {
            session()->setFlashdata('gagal', 'Shift tidak ditemukan');
            return redirect()->to(base_url('admin/shifts'));
        }

        $rules = [
            'lokasi_presensi_id' => [
                'rules' => 'required',
                'errors' => ['required' => 'Lokasi Presensi wajib dipilih'],
            ],
            'nama_shift' => [
                'rules' => 'required',
                'errors' => ['required' => 'Nama shift wajib diisi'],
            ],
            'jam_masuk' => [
                'rules' => 'required',
                'errors' => ['required' => 'Jam masuk wajib diisi'],
            ],
            'jam_keluar' => [
                'rules' => 'required',
                'errors' => ['required' => 'Jam keluar wajib diisi'],
            ],
        ];

        if (!$this->validate($rules)) {
            $lokasiPresensiModel = new LokasiPresensiModel();
            $data = [
                'title' => 'Edit Shift',
                'shift' => $shift,
                'lokasi_presensi' => $lokasiPresensiModel->findAll(),
                'validation' => \Config\Services::validation(),
            ];
            return view('admin/shifts/edit', $data);
        }

        $shiftModel->update($id, [
            'lokasi_presensi_id' => $this->request->getPost('lokasi_presensi_id'),
            'nama_shift' => $this->request->getPost('nama_shift'),
            'jam_masuk' => $this->request->getPost('jam_masuk'),
            'jam_keluar' => $this->request->getPost('jam_keluar'),
        ]);

        session()->setFlashData('berhasil', 'Shift berhasil diupdate');
        return redirect()->to(base_url('admin/shifts'));
    }

    public function delete($id)
    {
        $shiftModel = new ShiftModel();
        $shift = $shiftModel->find($id);

        if ($shift) {
            $shiftModel->delete($id);
            session()->setFlashData('berhasil', 'Shift berhasil dihapus');
        } else {
            session()->setFlashData('gagal', 'Shift tidak ditemukan');
        }

        return redirect()->to(base_url('admin/shifts'));
    }
}
