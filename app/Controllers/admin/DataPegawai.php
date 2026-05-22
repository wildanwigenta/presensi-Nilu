<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PegawaiModel;
use App\Models\UserModel;
use App\Models\LokasiPresensiModel;
use App\Models\JabatanModel;
use App\Models\ShiftModel;
use App\Models\PegawaiShiftModel;

class DataPegawai extends BaseController
{

    function __construct()
    {
        helper(['url', 'form']);
    }

    public function detail($id)
    {
        $pegawaiModel = new PegawaiModel();
        $data = [
            'title' => 'Detail Pegawai',
            'pegawai' => $pegawaiModel->detailPegawai($id),
        ];
        return view('admin/data_pegawai/detail', $data);
    }


    public function index()
    {
        $pegawaiModel = new PegawaiModel();
        $db = \Config\Database::connect();
        
        $builder = $db->table('pegawai');
        $builder->select('pegawai.*, users.username, users.status, users.role');
        $builder->join('users', 'users.id_pegawai = pegawai.id');
        $data = [
            'title' => 'Data Pegawai',
            'pegawai' => $builder->get()->getResultArray()
        ];
        
        // PERBAIKAN: Ubah dari 'admin/data_pegawai/index' menjadi 'admin/data_pegawai/data_pegawai'
        return view('admin/data_pegawai/data_pegawai', $data);
    }

    public function create()
    {
        $lokasi_presensi = new LokasiPresensiModel();
        $jabatan_model = new JabatanModel();
        $shiftModel = new ShiftModel();
        $shifts = $shiftModel
            ->select('shifts.*, lokasi_presensi.nama_lokasi')
            ->join('lokasi_presensi', 'lokasi_presensi.id = shifts.lokasi_presensi_id')
            ->orderBy('lokasi_presensi.nama_lokasi', 'ASC')
            ->orderBy('shifts.nama_shift', 'ASC')
            ->findAll();

        $data = [
            'title' => 'Tambah Pegawai',
            'lokasi_presensi' => $lokasi_presensi->findAll(),
            'jabatan' => $jabatan_model->orderBy('jabatan', 'ASC')->findAll(),
            'shifts' => $shifts,
            'validation' => \Config\Services::validation()
        ];
        return view('admin/data_pegawai/create', $data);
    }

    public function store()
    {
        $rules = [
            'nama' => [
                'rules' => 'required',
                'errors' => [
                    'required'=> "Nama Wajib Diisi"
                ],
            ],
            'jenis_kelamin' => [
                'rules' => 'required',
                'errors' => [
                    'required'=> "Jenis Kelamin Wajib Diisi"
                ],
            ],
            'alamat' => [
                'rules' => 'required',
                'errors' => [
                    'required'=> "Alamat Wajib Diisi"
                ],
            ],
            'no_hp' => [
                'rules' => 'required',
                'errors' => [
                    'required'=> "No HP Wajib Diisi"
                ],
            ],
            'jabatan' => [
                'rules' => 'required',
                'errors' => [
                    'required'=> "jabatan Wajib Diisi"
                ],
            ],
            'lokasi_presensi' => [
                'rules' => 'required',
                'errors' => [
                    'required'=> "Lokasi Presensi Wajib Diisi"
                ],
            ],
            'foto' => [
                'rules' => 'uploaded[foto]|max_size[foto,10240]|mime_in[foto,image/png,image/jpeg]',
                'errors' => [
                    'uploaded'=> "File Foto Wajib Diupload",
                    'max_size'=> "Ukuran Foto melebihi 10MB",
                    'mime_in' => "Jenis File yang diizinkan hanya PNG atau JPEG"
                ],
            ],
            'username' => [
                'rules' => 'required|is_unique[users.username]',
                'errors' => [
                    'required'=> "Username Wajib Diisi",
                    'is_unique' => "Username sudah terdaftar"
                ],
            ],
            'password' => [
                'rules' => 'required|min_length[6]',
                'errors' => [
                    'required'=> "Password Wajib Diisi",
                    'min_length' => "Password minimal 6 karakter"
                ],
            ],
            'konfirmasi_password' => [
                'rules' => 'required|matches[password]',
                'errors' => [
                    'required'=> "Konfirmasi password Wajib Diisi",
                    'matches' => "Konfirmasi Password tidak cocok"
                ],
            ],
            'role' => [
                'rules' => 'required',
                'errors' => [
                    'required'=> "Role Wajib Diisi"
                ],
            ],
        ];
        
        if(!$this->validate($rules)) {
            $lokasi_presensi = new LokasiPresensiModel();
            $jabatan_model = new JabatanModel();
            $shiftModel = new ShiftModel();
            $shifts = $shiftModel
                ->select('shifts.*, lokasi_presensi.nama_lokasi')
                ->join('lokasi_presensi', 'lokasi_presensi.id = shifts.lokasi_presensi_id')
                ->orderBy('lokasi_presensi.nama_lokasi', 'ASC')
                ->orderBy('shifts.nama_shift', 'ASC')
                ->findAll();
            $data = [
                'title' => 'Tambah Pegawai',
                'lokasi_presensi' => $lokasi_presensi->findAll(),
                'jabatan' => $jabatan_model->orderBy('jabatan', 'ASC')->findAll(),
                'shifts' => $shifts,
                'validation' => $this->validator
            ];
            return view('admin/data_pegawai/create', $data);
        } else {
            $pegawaiModel = new PegawaiModel();
            $nipBaru = $this->generateNIP();
            $shiftModel = new ShiftModel();

            $foto = $this->request->getFile('foto');
            if($foto->getError() == 4){
                $nama_foto = '';
            } else {
                $nama_foto = $foto->getRandomName();
                $foto->move('profile', $nama_foto);
            }

            // Insert ke tabel pegawai
            $pegawaiModel->insert([
                'nip' => $nipBaru,
                'nama' => $this->request->getPost('nama'),
                'jenis_kelamin' => $this->request->getPost('jenis_kelamin'),
                'alamat' => $this->request->getPost('alamat'),
                'no_hp' => $this->request->getPost('no_hp'),
                'jabatan' => $this->request->getPost('jabatan'),
                'lokasi_presensi' => $this->request->getPost('lokasi_presensi'),
                'foto' => $nama_foto,
                'face_descriptor' => $this->request->getPost('face_descriptor') ?: null,
            ]);

            $id_pegawai = $pegawaiModel->insertID();

            // Insert ke tabel users
            $userModel = new UserModel();
            $userModel->insert([
                'id_pegawai' => $id_pegawai, 
                'username' => $this->request->getPost('username'),
                'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
                'status' => 'Aktif',
                'role' => $this->request->getPost('role'),
            ]);

            $pegawaiShiftModel = new PegawaiShiftModel();
            $selectedShifts = $this->request->getPost('shift_ids');
            if (!is_array($selectedShifts)) {
                $selectedShifts = [];
            }
            $selectedShifts = array_filter(array_unique(array_map('intval', $selectedShifts)));

            if (!empty($selectedShifts)) {
                $validShifts = $shiftModel->whereIn('id', $selectedShifts)->countAllResults();
                if ($validShifts !== count($selectedShifts)) {
                    session()->setFlashData('gagal', 'Pilihan shift tidak valid.');
                    return redirect()->back()->withInput();
                }
            }

            foreach ($selectedShifts as $shiftId) {
                $pegawaiShiftModel->insert([
                    'pegawai_id' => $id_pegawai,
                    'shift_id' => $shiftId,
                ]);
            }
            
            session()->setFlashData('berhasil', 'Data Pegawai Berhasil Disimpan');
            return redirect()->to(base_url('admin/data_pegawai'));
        }
    }

    private function generateNIP()
    {
        $pegawaiModel = new PegawaiModel();
        $lastPegawai = $pegawaiModel->orderBy('id', 'DESC')->first();
        $lastNIP = $lastPegawai ? $lastPegawai['nip'] : 'P-0000';
        $lastNumber = (int) substr($lastNIP, 2);
        $newNumber = $lastNumber + 1;
        return 'P-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    public function edit($id)
    {
        $lokasi_presensi = new LokasiPresensiModel();
        $jabatan_model = new JabatanModel();
        $pegawaiModel = new PegawaiModel();
        $shiftModel = new ShiftModel();
        $pegawaiShiftModel = new PegawaiShiftModel();

        $assignedShifts = $pegawaiShiftModel->where('pegawai_id', $id)->findAll();
        $assignedShiftIds = array_map(function($item) {
            return $item['shift_id'];
        }, $assignedShifts);

        $shifts = $shiftModel
            ->select('shifts.*, lokasi_presensi.nama_lokasi')
            ->join('lokasi_presensi', 'lokasi_presensi.id = shifts.lokasi_presensi_id')
            ->orderBy('lokasi_presensi.nama_lokasi', 'ASC')
            ->orderBy('shifts.nama_shift', 'ASC')
            ->findAll();

        $data = [
            'title' => 'Edit Data Pegawai',
            'pegawai' => $pegawaiModel->editPegawai($id),
            'lokasi_presensi' => $lokasi_presensi->findAll(),
            'jabatan' => $jabatan_model->orderBy('jabatan', 'ASC')->findAll(),
            'shifts' => $shifts,
            'assigned_shift_ids' => $assignedShiftIds,
            'validation' => \Config\Services::validation()
        ];
        return view('admin/data_pegawai/edit', $data);
    }
    
    public function update($id)
    {
        $rules = [
            'nama' => [
                'rules' => 'required',
                'errors' => [
                    'required'=> "Nama Wajib Diisi"
                ],
            ],
            'jenis_kelamin' => [
                'rules' => 'required',
                'errors' => [
                    'required'=> "Jenis Kelamin Wajib Diisi"
                ],
            ],
            'alamat' => [
                'rules' => 'required',
                'errors' => [
                    'required'=> "Alamat Wajib Diisi"
                ],
            ],
            'no_hp' => [
                'rules' => 'required',
                'errors' => [
                    'required'=> "No HP Wajib Diisi"
                ],
            ],
            'jabatan' => [
                'rules' => 'required',
                'errors' => [
                    'required'=> "jabatan Wajib Diisi"
                ],
            ],
            'lokasi_presensi' => [
                'rules' => 'required',
                'errors' => [
                    'required'=> "Lokasi Presensi Wajib Diisi"
                ],
            ],
            'foto' => [
                'rules' => 'max_size[foto,10240]|mime_in[foto,image/png,image/jpeg]',
                'errors' => [
                    'max_size'=> "Ukuran Foto melebihi 10MB",
                    'mime_in' => "Jenis File yang diizinkan hanya PNG atau JPEG"
                ],
            ],
            'username' => [
                'rules' => 'required',
                'errors' => [
                    'required'=> "Username Wajib Diisi"
                ],
            ],
            'role' => [
                'rules' => 'required',
                'errors' => [
                    'required'=> "Role Wajib Diisi"
                ],
            ],
        ];
        
        if(!$this->validate($rules)) {
            $lokasi_presensi = new LokasiPresensiModel();
            $jabatan_model = new JabatanModel();
            $shiftModel = new ShiftModel();
            $pegawaiShiftModel = new PegawaiShiftModel();

            $assignedShifts = $pegawaiShiftModel->where('pegawai_id', $id)->findAll();
            $assignedShiftIds = array_map(function($item) {
                return $item['shift_id'];
            }, $assignedShifts);

            $shifts = $shiftModel
                ->select('shifts.*, lokasi_presensi.nama_lokasi')
                ->join('lokasi_presensi', 'lokasi_presensi.id = shifts.lokasi_presensi_id')
                ->orderBy('lokasi_presensi.nama_lokasi', 'ASC')
                ->orderBy('shifts.nama_shift', 'ASC')
                ->findAll();

            $data = [
                'title' => 'Edit Data Pegawai',
                'pegawai' => $pegawaiModel->editPegawai($id),
                'lokasi_presensi' => $lokasi_presensi->findAll(),
                'jabatan' => $jabatan_model->orderBy('jabatan', 'ASC')->findAll(),
                'shifts' => $shifts,
                'assigned_shift_ids' => $assignedShiftIds,
                'validation' => $this->validator
            ];
            return view('admin/data_pegawai/edit', $data);
        } else {
            $pegawaiModel = new PegawaiModel();
            $userModel = new UserModel();
            $shiftModel = new ShiftModel();

            $foto = $this->request->getFile('foto');

            if($foto->getError() == 4){
                $nama_foto = $this->request->getPost('foto_lama');
            } else {
                $nama_foto = $foto->getRandomName();
                $foto->move('profile', $nama_foto);
            }
            
            $updateData = [
                'nama' => $this->request->getPost('nama'),
                'jenis_kelamin' => $this->request->getPost('jenis_kelamin'),
                'alamat' => $this->request->getPost('alamat'),
                'no_hp' => $this->request->getPost('no_hp'),
                'jabatan' => $this->request->getPost('jabatan'),
                'lokasi_presensi' => $this->request->getPost('lokasi_presensi'),
                'foto' => $nama_foto,
            ];

            $faceDescriptor = $this->request->getPost('face_descriptor');
            if (!empty($faceDescriptor)) {
                $updateData['face_descriptor'] = $faceDescriptor;
            }

            $pegawaiModel->update($id, $updateData);

            if($this->request->getPost('password') == ''){
                $password = $this->request->getPost('password_lama');
            } else {
                $password = password_hash($this->request->getPost('password'), PASSWORD_DEFAULT);
            }

            $currentUser = $userModel->where('id_pegawai', $id)->first();
            $status = $this->request->getPost('status') ?? ($currentUser['status'] ?? 'Aktif');

            $userModel
                ->where('id_pegawai', $id)
                ->set([
                    'username' => $this->request->getPost('username'),
                    'password' => $password,
                    'status' => $status,
                    'role' => $this->request->getPost('role'), 
                ])
                ->update();

            $pegawaiShiftModel = new PegawaiShiftModel();
            $selectedShifts = $this->request->getPost('shift_ids');
            if (!is_array($selectedShifts)) {
                $selectedShifts = [];
            }
            $selectedShifts = array_filter(array_unique(array_map('intval', $selectedShifts)));

            if (!empty($selectedShifts)) {
                $validShifts = $shiftModel->whereIn('id', $selectedShifts)->countAllResults();
                if ($validShifts !== count($selectedShifts)) {
                    session()->setFlashData('gagal', 'Pilihan shift tidak valid.');
                    return redirect()->back()->withInput();
                }
            }

            $pegawaiShiftModel->where('pegawai_id', $id)->delete();
            foreach ($selectedShifts as $shiftId) {
                $pegawaiShiftModel->insert([
                    'pegawai_id' => $id,
                    'shift_id' => $shiftId,
                ]);
            }

            session()->setFlashData('berhasil', 'Data pegawai Berhasil Diupdate');
            return redirect()->to(base_url('admin/data_pegawai'));
        }
    }
    
    function delete($id)
    {
        $pegawaiModel = new PegawaiModel();
        $userModel = new UserModel();
        $pegawai = $pegawaiModel->find($id);
        if($pegawai){
            $userModel->where('id_pegawai', $id)->delete();
            $pegawaiModel->delete($id);
            
            session()->setFlashData('berhasil', 'Data Pegawai Berhasil Dihapus');
            return redirect()->to(base_url('admin/data_pegawai'));
        }
    }
}