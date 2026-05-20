<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\LokasiPresensiModel;
class LokasiPresensi extends BaseController
{
    public function index()
    {
        $lokasiPresensiModel = new LokasiPresensiModel();
        $data = [
            'title' => 'Data lokasi', 
            'lokasi_presensi' => $lokasiPresensiModel->findAll()
        ];
        return view('admin/lokasi_presensi/lokasi_presensi', $data);
    }
    public function detail($id)
    {
        $lokasiPresensiModel = new LokasiPresensiModel();
        $data = [
            'title' => 'Detail Lokasi Presensi',
            'lokasi_presensi' =>$lokasiPresensiModel->find($id),
        ];
        return view('admin/lokasi_presensi/detail', $data);
    }


    public function create()
    {
        $data = [
            'title' => 'Tambah Lokasi Presensi',
            'validation' => \Config\Services::validation()
        ];
        return view('admin/lokasi_presensi/create', $data);
    }
    public function store()
    {
        $rules = [
            'nama_lokasi' => [
                'rules' => 'required',
                'errors' => [
                'required'=> "Nama Lokasi Wajib Diisi"
                ],
            ],
             'alamat_lokasi' => [
                'rules' => 'required',
                'errors' => [
                'required'=> "Alamat Lokasi Wajib Diisi"
                ],
            ],
            'tipe_lokasi' => [
                'rules' => 'required',
                'errors' => [
                'required'=> "Tipe Lokasi Wajib Diisi"
                ],
            ],
            'latitude' => [
                'rules' => 'required',
                'errors' => [
                'required'=> "Latitude Wajib Diisi"
                ],
            ],
            'longitude' => [
                'rules' => 'required',
                'errors' => [
                'required'=> "longitude Wajib Diisi"
                ],
            ],
            'radius' => [
                'rules' => 'required',
                'errors' => [
                'required'=> "Radius Wajib Diisi"
                ],
            ],
            'zona_waktu' => [
                'rules' => 'required',
                'errors' => [
                'required'=> "Zona Waktu Wajib Diisi"
                ],
            ],
            'jam_masuk' => [
                'rules' => 'required',
                'errors' => [
                'required'=> "Jam Masuk Wajib Diisi"
                ],
            ],
            'jam_pulang' => [
                'rules' => 'required',
                'errors' => [
                'required'=> "Jam Pulang Wajib Diisi"
                ],
            ],
        ];
        if(!$this->validate($rules)) {
        $data = [
            'title' => 'Tambah Lokasi Presensi',
            'validation' => \Config\Services::validation()
        ];
            echo view('admin/lokasi_presensi/create', $data);
        }else{
           $lokasiPresensiModel = new LokasiPresensiModel();
           $lokasiPresensiModel->insert([
            'nama_lokasi' =>$this->request->getPost('nama_lokasi'),
            'alamat_lokasi' =>$this->request->getPost('alamat_lokasi'),
            'tipe_lokasi' =>$this->request->getPost('tipe_lokasi'),
            'latitude' =>$this->request->getPost('latitude'),
            'longitude' =>$this->request->getPost('longitude'),
            'radius' =>$this->request->getPost('radius'),
            'zona_waktu' =>$this->request->getPost('zona_waktu'),
            'jam_masuk' =>$this->request->getPost('jam_masuk'),
            'jam_pulang' =>$this->request->getPost('jam_pulang')
           ]);

           session()->setFlashData('berhasil', 'Data Lokasi Presensi Berhasil Disimpan');


           return redirect()->to(base_url('admin/lokasi_presensi')) ;
        }
    }

     public function edit($id)
    {
        $lokasiPresensiModel = new LokasiPresensiModel();
        $data = [
            'title' => 'Edit Lokasi Presensi',
            'lokasi_presensi' => $lokasiPresensiModel->find($id), 
            'validation' => \Config\Services::validation()
        ];
        return view('admin/lokasi_presensi/edit', $data);
    }
    public function update($id)
    {
        $lokasiPresensiModel = new LokasiPresensiModel();
         $rules = [
            'nama_lokasi' => [
                'rules' => 'required',
                'errors' => [
                'required'=> "Nama Lokasi Wajib Diisi"
                ],
            ],
             'alamat_lokasi' => [
                'rules' => 'required',
                'errors' => [
                'required'=> "Alamat Lokasi Wajib Diisi"
                ],
            ],
            'tipe_lokasi' => [
                'rules' => 'required',
                'errors' => [
                'required'=> "Tipe Lokasi Wajib Diisi"
                ],
            ],
            'latitude' => [
                'rules' => 'required',
                'errors' => [
                'required'=> "Latitude Wajib Diisi"
                ],
            ],
            'longitude' => [
                'rules' => 'required',
                'errors' => [
                'required'=> "longitude Wajib Diisi"
                ],
            ],
            'radius' => [
                'rules' => 'required',
                'errors' => [
                'required'=> "Radius Wajib Diisi"
                ],
            ],
            'zona_waktu' => [
                'rules' => 'required',
                'errors' => [
                'required'=> "Zona Waktu Wajib Diisi"
                ],
            ],
            'jam_masuk' => [
                'rules' => 'required',
                'errors' => [
                'required'=> "Jam Masuk Wajib Diisi"
                ],
            ],
            'jam_pulang' => [
                'rules' => 'required',
                'errors' => [
                'required'=> "Jam Pulang Wajib Diisi"
                ],
            ],
        ];
        if(!$this->validate($rules)) {
        $data = [
            'title' => 'Edit Lokasi Presensi',
            'lokasi_presensi' => $lokasiPresensiModel->find($id), 
            'validation' => \Config\Services::validation()
        ];
            echo view('admin/lokasi_presensi/edit', $data);
        }else{
           $lokasiPresensiModel = new LokasiPresensiModel();
           $lokasiPresensiModel->update($id, [
            'nama_lokasi' =>$this->request->getPost('nama_lokasi'),
            'alamat_lokasi' =>$this->request->getPost('alamat_lokasi'),
            'tipe_lokasi' =>$this->request->getPost('tipe_lokasi'),
            'latitude' =>$this->request->getPost('latitude'),
            'longitude' =>$this->request->getPost('longitude'),
            'radius' =>$this->request->getPost('radius'),
            'zona_waktu' =>$this->request->getPost('zona_waktu'),
            'jam_masuk' =>$this->request->getPost('jam_masuk'),
            'jam_pulang' =>$this->request->getPost('jam_pulang')
           ]);

           session()->setFlashData('berhasil', 'Data lokasi presensi Berhasil Diupdate');


           return redirect()->to(base_url('admin/lokasi_presensi')) ;
        }
    }
    function delete($id)
    {
        $lokasiPresensiModel = new LokasiPresensiModel();
        $lokasi_presensi = $lokasiPresensiModel->find($id);
        if($lokasi_presensi){
            $lokasiPresensiModel->delete($id);
            session()->setFlashData('berhasil', 'Data Lokasi Presensi Berhasil Dihapus');
            return redirect()->to(base_url('admin/lokasi_presensi')) ;
        }
    }
}
