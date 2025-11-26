<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Crawler</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background-color: #f5f5f5;
        }

        .container {
            background-color: white;
            padding: 30px;
            max-width: 800px;
            margin: 0 auto;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        pre {
            background-color: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Test Google Scholar Crawler</h1>

        <?php
        // Test dengan beberapa contoh
        $tests = [
            ['author' => 'Joko Siswantoro', 'keyword' => 'optimization', 'jumlah' => 5],
            ['author' => 'Andrew Ng', 'keyword' => 'machine learning', 'jumlah' => 3],
        ];

        echo "<h2>Contoh pencarian yang lebih baik:</h2>";
        echo "<ol>";
        foreach ($tests as $test) {
            echo "<li>";
            echo "Nama: <strong>{$test['author']}</strong>, ";
            echo "Keyword: <strong>{$test['keyword']}</strong>, ";
            echo "Jumlah: {$test['jumlah']}";
            echo "</li>";
        }
        echo "</ol>";

        echo "<h2>Tips:</h2>";
        echo "<ul>";
        echo "<li>Gunakan nama lengkap penulis (minimal nama depan + belakang)</li>";
        echo "<li>Gunakan keyword yang umum (1-2 kata)</li>";
        echo "<li>Keyword akan mencari artikel yang mengandung SALAH SATU kata</li>";
        echo "<li>Contoh keyword yang baik: 'optimization', 'algorithm', 'learning'</li>";
        echo "</ul>";

        // Test koneksi Python
        echo "<h2>Test Python:</h2>";
        echo "<pre>";
        $output = [];
        exec("python --version 2>&1", $output, $return_code);
        echo "Return code: $return_code\n";
        echo implode("\n", $output);
        echo "</pre>";

        // Test Chrome
        echo "<h2>Test Chrome Driver:</h2>";
        echo "<p>Chrome driver akan di-download otomatis saat pertama kali dijalankan.</p>";

        ?>

        <p><a href="index.php">← Kembali ke Pencarian</a></p>
    </div>
</body>

</html>