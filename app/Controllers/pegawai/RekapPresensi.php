<?php

namespace App\Controllers\Pegawai;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\PresensiModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class RekapPresensi extends BaseController
{
    public function index()
    {
        $presensiModel = new PresensiModel();
        $id_pegawai = session()->get('id_pegawai');
        $action = $this->request->getGet('action');
        $filter_tanggal = $this->request->getGet('filter_tanggal');
        
        // Jika action adalah export excel
        if($action == 'excel') {
            return $this->exportExcel($filter_tanggal, $id_pegawai);
        }
        
        // Tampilkan data dengan filter
        if($filter_tanggal) {
            $rekap_presensi = $presensiModel->rekap_presensi_pegawai_filter($filter_tanggal, $id_pegawai);
        } else {
            $rekap_presensi = $presensiModel->rekap_presensi_pegawai($id_pegawai);
        }
        
        $data = [
            'title' => 'Rekap Presensi',
            'rekap_presensi' => $rekap_presensi,
            'filter_tanggal' => $filter_tanggal
        ];
        return view('pegawai/rekap_presensi', $data);
    }
    
    private function exportExcel($filter_tanggal, $id_pegawai)
    {
        $presensiModel = new PresensiModel();
        
        if($filter_tanggal) {
            $rekap_presensi = $presensiModel->rekap_presensi_pegawai_filter($filter_tanggal, $id_pegawai);
        } else {
            $rekap_presensi = $presensiModel->rekap_presensi_pegawai($id_pegawai);
        }
        
        if(empty($rekap_presensi)) {
            session()->setFlashdata('error', 'Tidak ada data untuk diexport');
            return redirect()->to(base_url('pegawai/rekap_presensi'));
        }
        
        $spreadsheet = new Spreadsheet();
        $activeWorksheet = $spreadsheet->getActiveSheet();
        
        // Header
        $spreadsheet->getActiveSheet()->mergeCells('A1:H1');
        $activeWorksheet->setCellValue('A1', 'REKAP PRESENSI PEGAWAI');
        $activeWorksheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $activeWorksheet->getStyle('A1')->getAlignment()->setHorizontal('center');
        
        $activeWorksheet->setCellValue('A3', 'Tanggal Filter:');
        $activeWorksheet->setCellValue('B3', $filter_tanggal ?: 'Semua Tanggal');
        $activeWorksheet->setCellValue('A4', 'Waktu Export:');
        $activeWorksheet->setCellValue('B4', date('d-m-Y H:i:s'));
        
        // Header tabel
        $activeWorksheet->setCellValue('A6', 'NO');
        $activeWorksheet->setCellValue('B6', 'NAMA PEGAWAI');
        $activeWorksheet->setCellValue('C6', 'SHIFT');
        $activeWorksheet->setCellValue('D6', 'TANGGAL MASUK');
        $activeWorksheet->setCellValue('D6', 'JAM MASUK');
        $activeWorksheet->setCellValue('E6', 'TANGGAL KELUAR');
        $activeWorksheet->setCellValue('F6', 'JAM KELUAR');
        $activeWorksheet->setCellValue('G6', 'TOTAL JAM KERJA');
        $activeWorksheet->setCellValue('H6', 'TOTAL TERLAMBAT');
        
        // Style header
        $headerStyle = [
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E0E0E0']
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
            ]
        ];
        $activeWorksheet->getStyle('A6:H6')->applyFromArray($headerStyle);
        
        $rows = 7;
        $no = 1;
        foreach($rekap_presensi as $rekap){
            // Menghitung jumlah jam kerja
            $total_jam_kerja = '0 Jam 0 Menit';
            if($rekap['jam_masuk'] != '00:00:00' && $rekap['jam_keluar'] != '00:00:00') {
                $timestamp_jam_masuk = strtotime($rekap['tanggal_masuk'] . ' ' . $rekap['jam_masuk']);
                $timestamp_jam_keluar = strtotime($rekap['tanggal_keluar'] . ' ' . $rekap['jam_keluar']);
                $selisih = $timestamp_jam_keluar - $timestamp_jam_masuk;
                if($selisih > 0) {
                    $jam = floor($selisih / 3600);
                    $selisih -= $jam * 3600;
                    $menit = floor($selisih / 60);
                    $total_jam_kerja = $jam . ' Jam ' . $menit . ' Menit';
                }
            }
            
            // Menghitung total keterlambatan
            $total_terlambat = '0 Jam 0 Menit';
            if(isset($rekap['jam_masuk_kantor']) && $rekap['jam_masuk'] != '00:00:00') {
                $jam_masuk_real = strtotime($rekap['jam_masuk']);
                $jam_masuk_kantor = strtotime($rekap['jam_masuk_kantor']);
                $selisih_terlambat = $jam_masuk_real - $jam_masuk_kantor;
                if($selisih_terlambat > 0) {
                    $jam_terlambat = floor($selisih_terlambat / 3600);
                    $selisih_terlambat -= $jam_terlambat * 3600;
                    $menit_terlambat = floor($selisih_terlambat / 60);
                    $total_terlambat = $jam_terlambat . ' Jam ' . $menit_terlambat . ' Menit';
                } else {
                    $total_terlambat = 'On Time';
                }
            }
            
            $activeWorksheet->setCellValue('A' . $rows, $no++);
            $activeWorksheet->setCellValue('B' . $rows, $rekap['nama']);
            $activeWorksheet->setCellValue('C' . $rows, $rekap['nama_shift'] ?? '-');
            $activeWorksheet->setCellValue('D' . $rows, $rekap['tanggal_masuk']);
            $activeWorksheet->setCellValue('E' . $rows, $rekap['jam_masuk']);
            $activeWorksheet->setCellValue('F' . $rows, $rekap['jam_keluar']);
            $activeWorksheet->setCellValue('G' . $rows, $total_jam_kerja);
            $activeWorksheet->setCellValue('H' . $rows, $total_terlambat);
            
            $rows++;
        }
        
        // Auto-size columns
        foreach(range('A', 'H') as $col) {
            $activeWorksheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Set headers untuk download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="rekap_presensi_pegawai_' . date('Y-m-d') . '.xlsx"');
        header('Cache-Control: max-age=0');
        
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit();
    }
}