<?php
// process.php - Menerima input dari form dan menjalankan Python script

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $author = $_POST['author'] ?? '';
    $keyword = $_POST['keyword'] ?? '';
    $jumlah = $_POST['jumlah'] ?? 5;

    // Validasi input
    if (empty($author) || empty($keyword) || empty($jumlah)) {
        die("Error: Semua field harus diisi!");
    }

    // Escape input untuk keamanan
    $author = escapeshellarg($author);
    $keyword = escapeshellarg($keyword);
    $jumlah = intval($jumlah);

    // Path ke Python executable (untuk Windows/Laragon)
    // Coba beberapa kemungkinan path Python
    $pythonPath = "python"; // Default untuk Windows dengan Python di PATH
    // Jika error, coba: "py" atau "C:\\Python311\\python.exe" (sesuaikan versi)

    // Path ke script Python
    $scriptPath = __DIR__ . "/scholar_crawler.py";

    // File output JSON
    $outputFile = __DIR__ . "/results.json";

    // Hapus file hasil sebelumnya jika ada
    if (file_exists($outputFile)) {
        unlink($outputFile);
    }

    // Jalankan Python script
    $command = "$pythonPath \"$scriptPath\" $author $keyword $jumlah 2>&1";

    echo "<html><head><title>Processing...</title></head><body>";
    echo "<h2>Sedang memproses...</h2>";
    echo "<p>Mencari artikel dari penulis: " . htmlspecialchars($_POST['author']) . "</p>";
    echo "<p>Keyword: " . htmlspecialchars($_POST['keyword']) . "</p>";
    echo "<p>Jumlah data: $jumlah</p>";
    echo "<pre>";

    // Eksekusi command
    $output = [];
    $returnCode = 0;
    exec($command, $output, $returnCode);

    // Tampilkan output untuk debugging (optional, bisa dicomment kalau tidak perlu)
    echo implode("\n", $output);
    echo "</pre>";

    // Check jika berhasil
    if ($returnCode === 0 && file_exists($outputFile)) {
        echo "<h3 style='color: green;'>Crawling berhasil!</h3>";
        echo "<p>Redirecting to results...</p>";
        echo "<script>setTimeout(function(){ window.location.href='result.php'; }, 2000);</script>";
        echo "<p>Jika tidak redirect otomatis, <a href='result.php'>klik di sini</a></p>";
    } else {
        echo "<h3 style='color: red;'>Error terjadi saat crawling!</h3>";
        echo "<p>Return code: $returnCode</p>";
        echo "<p><a href='index.php'>Kembali</a></p>";
    }

    echo "</body></html>";
} else {
    header("Location: index.php");
    exit();
}
