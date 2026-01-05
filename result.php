<?php
$jsonFile = __DIR__ . '/results.json';
$maxWait = 30; // detik
$start = time();

while (!file_exists($jsonFile)) {
    if (time() - $start > $maxWait) {
        break;
    }
    sleep(1);
}

if (!file_exists($jsonFile)) {
    echo "<h2>⏳ Proses masih berjalan...</h2>";
    echo "<p>Silakan refresh halaman ini setelah CAPTCHA selesai.</p>";
    echo "<a href='result.php'>Refresh</a>";
    exit;
}

$data = json_decode(file_get_contents($jsonFile), true);

if (!$data || !isset($data['results'])) {
    die("Data hasil tidak valid.");
}

$searchParams = $data['search_params'];
$results = $data['results'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Hasil Crawling Scholar</title>
    <style>
        body { font-family: Arial; background:#f5f5f5; padding:20px; }
        table { width:100%; border-collapse:collapse; background:#fff; }
        th, td { padding:10px; border:1px solid #ddd; }
        th { background:#eee; }
    </style>
</head>
<body>

<h2>Hasil Pencarian Google Scholar</h2>
<p><b>Penulis:</b> <?= htmlspecialchars($searchParams['author']) ?></p>
<p><b>Keyword:</b> <?= htmlspecialchars($searchParams['keyword']) ?></p>
<p><b>Total Data:</b> <?= count($results) ?></p>

<table>
<tr>
    <th>Judul Artikel</th>
    <th>Penulis</th>
    <th>Tanggal Rilis</th>
    <th>Nama Jurnal</th>
    <th>Jumlah Sitasi</th>
    <th>Link Jurnal</th>
    <th>Similarity Value</th>
</tr>

<?php foreach ($results as $r): ?>
<tr>
    <td><?= htmlspecialchars($r['title']) ?></td>
    <td><?= htmlspecialchars($r['authors']) ?></td>
    <td><?= htmlspecialchars($r['publish_date']) ?></td>
    <td><?= htmlspecialchars($r['journal']) ?></td>
    <td><?= htmlspecialchars($r['citations']) ?></td>
    <td>
        <?php if ($r['link'] !== 'N/A'): ?>
            <a href="<?= htmlspecialchars($r['link']) ?>" target="_blank">Link</a>
        <?php else: ?>
            N/A
        <?php endif; ?>
    </td>
    <td><?= htmlspecialchars($r['tfidf_similarity']) ?></td>
</tr>
<?php endforeach; ?>
</table>

<br>
<a href="index.php">« Kembali</a>

</body>
</html>
