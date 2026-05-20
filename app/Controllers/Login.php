<?php

namespace App\Controllers;
use App\Models\LoginModel;
use App\Models\PegawaiModel;
use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Login extends BaseController
{
    
    public function index()
    {
        $data = [
            'validation' => \Config\Services::validation()
        ];
        return view('login', $data);
    }

    public function login_action()
    { 
        $rules = [
             'username' => 'required',
             'password' => 'required'
        ];
        if(!$this->validate($rules)){
            $data['validation'] = $this->validator;
            return view('login', $data);
        }else{
            $session = session();
            $LoginModel = new LoginModel;

            $username = $this->request->getVar('username');
            $password = $this->request->getVar('password');
            $cekusername = $LoginModel->where('username', $username)->first();
           

            if($cekusername){
                $password_db = $cekusername['password'];
                $cek_password = password_verify($password, $password_db);
                if($cek_password){

                $session_data = [
                    'username' => $cekusername['username'],
                    'logged_in' => TRUE,
                    'role_id' => $cekusername['role'],
                    'id_users' => $cekusername['id'], // ID dari tabel users
                    'id_pegawai' => $cekusername['id_pegawai'] // ID dari tabel pegawai
                ];
                $session->set($session_data);
                
                switch($cekusername['role']){
                    case "admin":
                        return redirect()->to('admin/home');
                    case "pegawai":
                        return redirect()->to('pegawai/home');    
                    default:
                        $session->setFlashdata('pesan','Akun anda belum terdaftar');
                        return redirect()->to('/');
                }
                }else{
                    $session->setFlashdata('pesan','Password salah, silakan coba kembali');
                    return redirect()->to('/');
                }
            }else{
                $session->setFlashdata('pesan','Username salah, silakan coba kembali');
                return redirect()->to('/');
            }
        }
    } 
    
    public function logout()
    {
        $session = session();
        $session->destroy();
        return redirect()->to('/');
    }
}