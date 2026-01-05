<?php
// process.php - menjalankan crawler Python (ASYNC)

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

// =====================
// 1. Ambil & validasi input
// =====================
$author  = trim($_POST['author'] ?? '');
$keyword = trim($_POST['keyword'] ?? '');
$jumlah  = intval($_POST['jumlah'] ?? 5);

if ($author === '' || $keyword === '' || $jumlah <= 0) {
    die("Error: Input tidak valid.");
}

// =====================
// 2. Escape input
// =====================
$authorArg  = escapeshellarg($author);
$keywordArg = escapeshellarg($keyword);

// =====================
// 3. Path Python & Script
// =====================
$pythonPath = "python"; 
// jika gagal di Laragon, ganti:
// $pythonPath = "py";
// atau full path:
// $pythonPath = "C:\\Users\\YOURNAME\\AppData\\Local\\Programs\\Python\\Python311\\python.exe";

$scriptPath = __DIR__ . "/scholar_crawler.py";
$jsonFile   = __DIR__ . "/results.json";
$logFile    = __DIR__ . "/crawler.log";

// =====================
// 4. Hapus hasil lama
// =====================
if (file_exists($jsonFile)) {
    unlink($jsonFile);
}

// =====================
// 5. Jalankan Python di BACKGROUND
// =====================
$command = "$pythonPath \"$scriptPath\" $authorArg $keywordArg $jumlah > \"$logFile\" 2>&1 &";
exec($command);

// =====================
// 6. Redirect LANGSUNG ke result.php
// =====================
header("Location: result.php");
exit;
