<?php

namespace App\Models;

use CodeIgniter\Model;

class LoginModel extends Model
{
    protected $table = 'users';
    protected $allowedFields = ['id_pegawai', 'username', 'password', 'status', 'role'];
    
    // Untuk join dengan tabel pegawai jika diperlukan
    public function getUserWithPegawai($username)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('users');
        $builder->select('users.*, pegawai.nama, pegawai.nip');
        $builder->join('pegawai', 'pegawai.id = users.id_pegawai');
        $builder->where('users.username', $username);
        return $builder->get()->getRowArray();
    }
}