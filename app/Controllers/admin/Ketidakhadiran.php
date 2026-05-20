<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\KetidakhadiranModel;
use CodeIgniter\HTTP\ResponseInterface;

class Ketidakhadiran extends BaseController
{

     public function index()
    {
        $ketidakhadiranModel = new KetidakhadiranModel();
        $data = [
            'title' => "Ketidakhadiran",
            'ketidakhadiran' => $ketidakhadiranModel->findAll()
        ];
        return view('admin/ketidakhadiran', $data);
    }
    public function approved($id)
    {
         $ketidakhadiranModel = new KetidakhadiranModel();
        
           $ketidakhadiranModel->update($id, [
            'status' => 'Approved' ,
           ]);

           session()->setFlashData('berhasil', 'Pengajuan Ketidakhadiran Berhasil Di approved');
           return redirect()->to(base_url('admin/ketidakhadiran')) ;
    }
}
