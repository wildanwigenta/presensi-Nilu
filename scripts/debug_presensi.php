<?php
// Load CodeIgniter framework
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/Config/Constants.php';

$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Initialize CodeIgniter
$app = \Config\Services::codeigniter();
$app->initialize();

$db = \Config\Database::connect();
$builder = $db->table('presensi');
$results = $builder->limit(10)->get()->getResultArray();

echo "=== Data Presensi (10 Record Terakhir) ===\n";
echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;

echo "\n=== Field Check ===\n";
if (!empty($results)) {
    foreach ($results as $i => $row) {
        echo "Record " . ($i + 1) . ":\n";
        echo "  jam_masuk: '" . ($row['jam_masuk'] ?? 'NULL') . "'\n";
        echo "  jam_keluar: '" . ($row['jam_keluar'] ?? 'NULL') . "'\n";
        echo "  tanggal_masuk: '" . ($row['tanggal_masuk'] ?? 'NULL') . "'\n";
        echo "  tanggal_keluar: '" . ($row['tanggal_keluar'] ?? 'NULL') . "'\n";
        echo "\n";
    }
}
