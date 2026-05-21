<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\PresensiModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class RekapPresensi extends BaseController
{
    public function rekap_harian()
    {
        $presensi_model = new PresensiModel();
        $filter_tanggal = $this->request->getGet('filter_tanggal');
        $action = $this->request->getGet('action');
        
        // Jika action adalah export excel
        if($action == 'excel') {
            return $this->exportHarianExcel($filter_tanggal);
        }
        
        // Tampilkan data dengan filter
        if($filter_tanggal) {
            $rekap_harian = $presensi_model->rekap_harian_filter($filter_tanggal);
        } else {
            $rekap_harian = $presensi_model->rekap_harian();
        }

        $data = [
            'title' => 'Rekap Harian',
            'tanggal' => $filter_tanggal,
            'rekap_harian' => $rekap_harian
        ];
        return view('admin/rekap_presensi/rekap_harian', $data);
    }

    private function exportHarianExcel($filter_tanggal)
    {
        $presensi_model = new PresensiModel();
        
        if($filter_tanggal) {
            $rekap_harian = $presensi_model->rekap_harian_filter($filter_tanggal);
        } else {
            $rekap_harian = $presensi_model->rekap_harian();
        }
        
        if(empty($rekap_harian)) {
            session()->setFlashdata('error', 'Tidak ada data untuk diexport');
            return redirect()->to(base_url('admin/rekap_harian'));
        }
        
        $spreadsheet = new Spreadsheet();
        $activeWorksheet = $spreadsheet->getActiveSheet();
        
        // Header
        $spreadsheet->getActiveSheet()->mergeCells('A1:H1');
        $activeWorksheet->setCellValue('A1', 'REKAP PRESENSI HARIAN');
        $activeWorksheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $activeWorksheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $activeWorksheet->setCellValue('A3', 'Tanggal:');
        $activeWorksheet->setCellValue('B3', $filter_tanggal ? date('d F Y', strtotime($filter_tanggal)) : date('d F Y'));
        $activeWorksheet->setCellValue('A4', 'Waktu Export:');
        $activeWorksheet->setCellValue('B4', date('d-m-Y H:i:s'));
        
        // Header tabel
        $activeWorksheet->setCellValue('A6', 'NO');
        $activeWorksheet->setCellValue('B6', 'NAMA PEGAWAI');
        $activeWorksheet->setCellValue('C6', 'SHIFT');
        $activeWorksheet->setCellValue('D6', 'TANGGAL MASUK');
        $activeWorksheet->setCellValue('E6', 'JAM MASUK');
        $activeWorksheet->setCellValue('F6', 'JAM KELUAR');
        $activeWorksheet->setCellValue('G6', 'TOTAL JAM KERJA');
        $activeWorksheet->setCellValue('H6', 'TOTAL TERLAMBAT');
        
        // Style header
        $headerStyle = [
            'font' => ['bold' => true, 'size' => 11],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E0E0E0']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN
                ]
            ]
        ];
        $activeWorksheet->getStyle('A6:H6')->applyFromArray($headerStyle);
        
        $rows = 7;
        $no = 1;
        foreach($rekap_harian as $rekap){
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
        header('Content-Disposition: attachment;filename="rekap_presensi_harian_' . date('Y-m-d') . '.xlsx"');
        header('Cache-Control: max-age=0');
        
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit();
    }

    public function rekap_bulanan()
    {
        $presensi_model = new PresensiModel();
        $filter_bulan = $this->request->getGet('filter_bulan');
        $filter_tahun = $this->request->getGet('filter_tahun');
        $action = $this->request->getGet('action');
        
        // Jika action adalah export excel
        if($action == 'excel') {
            return $this->exportBulananExcel($filter_bulan, $filter_tahun);
        }
        
        // Tampilkan data dengan filter
        if($filter_bulan && $filter_tahun) {
            $rekap_bulanan = $presensi_model->rekap_bulanan_filter($filter_bulan, $filter_tahun);
        } else {
            $rekap_bulanan = $presensi_model->rekap_bulanan();
        }

        $data = [
            'title' => 'Rekap Bulanan',
            'bulan' => $filter_bulan,
            'tahun' => $filter_tahun,
            'rekap_bulanan' => $rekap_bulanan
        ];
        return view('admin/rekap_presensi/rekap_bulanan', $data);
    }

    private function exportBulananExcel($filter_bulan, $filter_tahun)
    {
        $presensi_model = new PresensiModel();
        
        if($filter_bulan && $filter_tahun) {
            $rekap_bulanan = $presensi_model->rekap_bulanan_filter($filter_bulan, $filter_tahun);
        } else {
            $rekap_bulanan = $presensi_model->rekap_bulanan();
        }
        
        if(empty($rekap_bulanan)) {
            session()->setFlashdata('error', 'Tidak ada data untuk diexport');
            return redirect()->to(base_url('admin/rekap_bulanan'));
        }
        
        $spreadsheet = new Spreadsheet();
        $activeWorksheet = $spreadsheet->getActiveSheet();
        
        // Header
        $spreadsheet->getActiveSheet()->mergeCells('A1:H1');
        $activeWorksheet->setCellValue('A1', 'REKAP PRESENSI BULANAN');
        $activeWorksheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $activeWorksheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $activeWorksheet->setCellValue('A3', 'Bulan:');
        $bulan_text = '';
        if($filter_bulan && $filter_tahun) {
            $bulan_text = date('F Y', strtotime($filter_tahun . '-' . $filter_bulan));
        } else {
            $bulan_text = date('F Y');
        }
        $activeWorksheet->setCellValue('B3', $bulan_text);
        $activeWorksheet->setCellValue('A4', 'Waktu Export:');
        $activeWorksheet->setCellValue('B4', date('d-m-Y H:i:s'));
        
        // Header tabel
        $activeWorksheet->setCellValue('A6', 'NO');
        $activeWorksheet->setCellValue('B6', 'NAMA PEGAWAI');
        $activeWorksheet->setCellValue('C6', 'SHIFT');
        $activeWorksheet->setCellValue('D6', 'TANGGAL MASUK');
        $activeWorksheet->setCellValue('E6', 'JAM MASUK');
        $activeWorksheet->setCellValue('F6', 'JAM KELUAR');
        $activeWorksheet->setCellValue('G6', 'TOTAL JAM KERJA');
        $activeWorksheet->setCellValue('H6', 'TOTAL TERLAMBAT');
        
        // Style header
        $headerStyle = [
            'font' => ['bold' => true, 'size' => 11],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E0E0E0']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN
                ]
            ]
        ];
        $activeWorksheet->getStyle('A6:H6')->applyFromArray($headerStyle);
        
        $rows = 7;
        $no = 1;
        foreach($rekap_bulanan as $rekap){
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
        header('Content-Disposition: attachment;filename="rekap_presensi_bulanan_' . date('Y-m-d') . '.xlsx"');
        header('Cache-Control: max-age=0');
        
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit();
    }
}