<?php

namespace App\Controllers\Pegawai;

use App\Controllers\BaseController;
use App\Models\PresensiModel;
use App\Helpers\TimeHelper;
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

    if ($filter_tanggal) {
        $rekap_presensi = $presensiModel->rekap_presensi_pegawai_filter($filter_tanggal, $id_pegawai);
    } else {
        $rekap_presensi = $presensiModel->rekap_presensi_pegawai($id_pegawai);
    }

    if (empty($rekap_presensi)) {
        session()->setFlashdata('error', 'Tidak ada data untuk diexport');
        return redirect()->to(base_url('pegawai/rekap_presensi'));
    }

    $spreadsheet    = new Spreadsheet();
    $activeWorksheet = $spreadsheet->getActiveSheet();

    // Judul
    $spreadsheet->getActiveSheet()->mergeCells('A1:H1');
    $activeWorksheet->setCellValue('A1', 'REKAP PRESENSI PEGAWAI');
    $activeWorksheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
    $activeWorksheet->getStyle('A1')->getAlignment()->setHorizontal('center');

    $activeWorksheet->setCellValue('A3', 'Tanggal Filter:');
    $activeWorksheet->setCellValue('B3', $filter_tanggal ?: 'Semua Tanggal');
    $activeWorksheet->setCellValue('A4', 'Waktu Export:');
    $activeWorksheet->setCellValue('B4', date('d-m-Y H:i:s'));

    // Header tabel (fix: D6 tidak duplikat lagi)
    $activeWorksheet->setCellValue('A6', 'NO');
    $activeWorksheet->setCellValue('B6', 'NAMA PEGAWAI');
    $activeWorksheet->setCellValue('C6', 'SHIFT');
    $activeWorksheet->setCellValue('D6', 'TANGGAL');
    $activeWorksheet->setCellValue('E6', 'JAM MASUK');
    $activeWorksheet->setCellValue('F6', 'JAM KELUAR');
    $activeWorksheet->setCellValue('G6', 'TOTAL JAM KERJA');
    $activeWorksheet->setCellValue('H6', 'TOTAL TERLAMBAT');

    $headerStyle = [
        'font' => ['bold' => true],
        'fill' => [
            'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'startColor' => ['rgb' => 'E0E0E0']
        ],
        'alignment' => [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
        ]
    ];
    $activeWorksheet->getStyle('A6:H6')->applyFromArray($headerStyle);

    $rows = 7;
    $no   = 1;
    foreach ($rekap_presensi as $rekap) {
        // Hitung total jam kerja
        $total_jam_kerja = '-';
        if (!empty($rekap['jam_masuk']) && !empty($rekap['jam_keluar'])) {
            $masuk  = new \DateTime($rekap['tanggal_masuk'] . ' ' . $rekap['jam_masuk']);
            $keluar = new \DateTime(($rekap['tanggal_keluar'] ?: $rekap['tanggal_masuk']) . ' ' . $rekap['jam_keluar']);

            if ($keluar < $masuk) {
                $keluar->modify('+1 day');
            }

            $diff            = $masuk->diff($keluar);
            $total_jam_kerja = ($diff->h + ($diff->days * 24)) . ' jam ' . $diff->i . ' menit';
        }

        // Hitung keterlambatan
        $total_terlambat = '-';
        if (!empty($rekap['jam_masuk']) && !empty($rekap['jam_masuk_kantor'])) {
            $jam_masuk  = new \DateTime($rekap['tanggal_masuk'] . ' ' . $rekap['jam_masuk']);
            $jam_kantor = new \DateTime($rekap['tanggal_masuk'] . ' ' . $rekap['jam_masuk_kantor']);

            $total_terlambat = ($jam_masuk > $jam_kantor)
                ? ($jam_kantor->diff($jam_masuk)->h . ' jam ' . $jam_kantor->diff($jam_masuk)->i . ' menit')
                : 'On Time';
        }

        $activeWorksheet->setCellValue('A' . $rows, $no++);
        $activeWorksheet->setCellValue('B' . $rows, $rekap['nama']);
        $activeWorksheet->setCellValue('C' . $rows, $rekap['nama_shift'] ?? '-');
        $activeWorksheet->setCellValue('D' . $rows, $rekap['tanggal_masuk']);
        $activeWorksheet->setCellValue('E' . $rows, $rekap['jam_masuk']);
        $activeWorksheet->setCellValue('F' . $rows, $rekap['jam_keluar'] ?: '-');
        $activeWorksheet->setCellValue('G' . $rows, $total_jam_kerja);
        $activeWorksheet->setCellValue('H' . $rows, $total_terlambat);

        $rows++;
    }

    foreach (range('A', 'H') as $col) {
        $activeWorksheet->getColumnDimension($col)->setAutoSize(true);
    }

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="rekap_presensi_' . date('Y-m-d') . '.xlsx"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit();
}
}