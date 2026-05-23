<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PresensiModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class RekapPresensi extends BaseController
{
    /**
     * Helper: hitung total jam kerja
     */
    private function hitungJamKerja($tanggal_masuk, $jam_masuk, $tanggal_keluar, $jam_keluar): string
    {
        if (empty($jam_masuk) || empty($jam_keluar)) {
            return '-';
        }

        $masuk  = new \DateTime($tanggal_masuk . ' ' . $jam_masuk);
        $keluar = new \DateTime(($tanggal_keluar ?: $tanggal_masuk) . ' ' . $jam_keluar);

        // Handle lintas tengah malam
        if ($keluar < $masuk) {
            $keluar->modify('+1 day');
        }

        $diff = $masuk->diff($keluar);
        $total_jam = ($diff->days * 24) + $diff->h;

        return $total_jam . ' Jam ' . $diff->i . ' Menit';
    }

    /**
     * Helper: hitung keterlambatan
     */
    private function hitungKeterlambatan($tanggal_masuk, $jam_masuk, $jam_masuk_kantor): string
    {
        if (empty($jam_masuk) || empty($jam_masuk_kantor)) {
            return '-';
        }

        $masuk   = new \DateTime($tanggal_masuk . ' ' . $jam_masuk);
        $kantor  = new \DateTime($tanggal_masuk . ' ' . $jam_masuk_kantor);

        if ($masuk <= $kantor) {
            return 'On Time';
        }

        $diff = $kantor->diff($masuk);
        return $diff->h . ' Jam ' . $diff->i . ' Menit';
    }

    // =========================================================
    // REKAP HARIAN
    // =========================================================

    public function rekap_harian()
    {
        $presensi_model = new PresensiModel();
        $filter_tanggal = $this->request->getGet('filter_tanggal');
        $action         = $this->request->getGet('action');

        if ($action == 'excel') {
            return $this->exportHarianExcel($filter_tanggal);
        }

        $rekap_harian = $filter_tanggal
            ? $presensi_model->rekap_harian_filter($filter_tanggal)
            : $presensi_model->rekap_harian();

        $data = [
            'title'        => 'Rekap Harian',
            'tanggal'      => $filter_tanggal,
            'rekap_harian' => $rekap_harian
        ];
        return view('admin/rekap_presensi/rekap_harian', $data);
    }

    private function exportHarianExcel($filter_tanggal)
    {
        $presensi_model = new PresensiModel();

        $rekap_harian = $filter_tanggal
            ? $presensi_model->rekap_harian_filter($filter_tanggal)
            : $presensi_model->rekap_harian();

        if (empty($rekap_harian)) {
            session()->setFlashdata('error', 'Tidak ada data untuk diexport');
            return redirect()->to(base_url('admin/rekap_harian'));
        }

        $spreadsheet     = new Spreadsheet();
        $activeWorksheet = $spreadsheet->getActiveSheet();

        // Judul
        $activeWorksheet->mergeCells('A1:H1');
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
        $activeWorksheet->setCellValue('D6', 'TANGGAL');
        $activeWorksheet->setCellValue('E6', 'JAM MASUK');
        $activeWorksheet->setCellValue('F6', 'JAM KELUAR');
        $activeWorksheet->setCellValue('G6', 'TOTAL JAM KERJA');
        $activeWorksheet->setCellValue('H6', 'TOTAL TERLAMBAT');

        $headerStyle = [
            'font'      => ['bold' => true, 'size' => 11],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E0E0E0']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $activeWorksheet->getStyle('A6:H6')->applyFromArray($headerStyle);

        $rows = 7;
        $no   = 1;
        foreach ($rekap_harian as $rekap) {
            $total_jam_kerja = $this->hitungJamKerja(
                $rekap['tanggal_masuk'] ?? '',
                $rekap['jam_masuk'] ?? '',
                $rekap['tanggal_keluar'] ?? '',
                $rekap['jam_keluar'] ?? ''
            );

            $total_terlambat = $this->hitungKeterlambatan(
                $rekap['tanggal_masuk'] ?? '',
                $rekap['jam_masuk'] ?? '',
                $rekap['jam_masuk_kantor'] ?? ''
            );

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
        header('Content-Disposition: attachment;filename="rekap_presensi_harian_' . date('Y-m-d') . '.xlsx"');
        header('Cache-Control: max-age=0');

        (new Xlsx($spreadsheet))->save('php://output');
        exit();
    }

    // =========================================================
    // REKAP BULANAN
    // =========================================================

    public function rekap_bulanan()
    {
        $presensi_model = new PresensiModel();
        $filter_bulan   = $this->request->getGet('filter_bulan');
        $filter_tahun   = $this->request->getGet('filter_tahun');
        $action         = $this->request->getGet('action');

        if ($action == 'excel') {
            return $this->exportBulananExcel($filter_bulan, $filter_tahun);
        }

        $rekap_bulanan = ($filter_bulan && $filter_tahun)
            ? $presensi_model->rekap_bulanan_filter($filter_bulan, $filter_tahun)
            : $presensi_model->rekap_bulanan();

        $data = [
            'title'         => 'Rekap Bulanan',
            'bulan'         => $filter_bulan,
            'tahun'         => $filter_tahun,
            'rekap_bulanan' => $rekap_bulanan
        ];
        return view('admin/rekap_presensi/rekap_bulanan', $data);
    }

    private function exportBulananExcel($filter_bulan, $filter_tahun)
    {
        $presensi_model = new PresensiModel();

        $rekap_bulanan = ($filter_bulan && $filter_tahun)
            ? $presensi_model->rekap_bulanan_filter($filter_bulan, $filter_tahun)
            : $presensi_model->rekap_bulanan();

        if (empty($rekap_bulanan)) {
            session()->setFlashdata('error', 'Tidak ada data untuk diexport');
            return redirect()->to(base_url('admin/rekap_bulanan'));
        }

        $spreadsheet     = new Spreadsheet();
        $activeWorksheet = $spreadsheet->getActiveSheet();

        // Judul
        $activeWorksheet->mergeCells('A1:H1');
        $activeWorksheet->setCellValue('A1', 'REKAP PRESENSI BULANAN');
        $activeWorksheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $activeWorksheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $bulan_text = ($filter_bulan && $filter_tahun)
            ? date('F Y', strtotime($filter_tahun . '-' . $filter_bulan . '-01'))
            : date('F Y');

        $activeWorksheet->setCellValue('A3', 'Bulan:');
        $activeWorksheet->setCellValue('B3', $bulan_text);
        $activeWorksheet->setCellValue('A4', 'Waktu Export:');
        $activeWorksheet->setCellValue('B4', date('d-m-Y H:i:s'));

        // Header tabel
        $activeWorksheet->setCellValue('A6', 'NO');
        $activeWorksheet->setCellValue('B6', 'NAMA PEGAWAI');
        $activeWorksheet->setCellValue('C6', 'SHIFT');
        $activeWorksheet->setCellValue('D6', 'TANGGAL');
        $activeWorksheet->setCellValue('E6', 'JAM MASUK');
        $activeWorksheet->setCellValue('F6', 'JAM KELUAR');
        $activeWorksheet->setCellValue('G6', 'TOTAL JAM KERJA');
        $activeWorksheet->setCellValue('H6', 'TOTAL TERLAMBAT');

        $headerStyle = [
            'font'      => ['bold' => true, 'size' => 11],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E0E0E0']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $activeWorksheet->getStyle('A6:H6')->applyFromArray($headerStyle);

        $rows = 7;
        $no   = 1;
        foreach ($rekap_bulanan as $rekap) {
            $total_jam_kerja = $this->hitungJamKerja(
                $rekap['tanggal_masuk'] ?? '',
                $rekap['jam_masuk'] ?? '',
                $rekap['tanggal_keluar'] ?? '',
                $rekap['jam_keluar'] ?? ''
            );

            $total_terlambat = $this->hitungKeterlambatan(
                $rekap['tanggal_masuk'] ?? '',
                $rekap['jam_masuk'] ?? '',
                $rekap['jam_masuk_kantor'] ?? ''
            );

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
        header('Content-Disposition: attachment;filename="rekap_presensi_bulanan_' . date('Y-m-d') . '.xlsx"');
        header('Cache-Control: max-age=0');

        (new Xlsx($spreadsheet))->save('php://output');
        exit();
    }
}