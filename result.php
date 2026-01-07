<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Pencarian Artikel</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background-color: #f5f5f5;
        }

        .container {
            background-color: white;
            padding: 30px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        h1 {
            font-size: 24px;
            margin-bottom: 10px;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #0066cc;
            text-decoration: none;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .info {
            margin-bottom: 20px;
        }

        .info p {
            margin: 5px 0;
            font-weight: bold;
        }

        h2 {
            font-size: 18px;
            margin: 20px 0 10px 0;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th {
            background-color: #f0f0f0;
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
            font-weight: bold;
        }

        td {
            padding: 12px;
            border: 1px solid #ddd;
            vertical-align: top;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .link-cell a {
            color: #0066cc;
            word-break: break-all;
        }

        .no-data {
            text-align: center;
            padding: 30px;
            color: #999;
        }

        .error {
            background-color: #ffebee;
            color: #c62828;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="container">
        <a href="index.php" class="back-link">
            < Back to Home</a>

                <?php
                $jsonFile = __DIR__ . '/results.json';

                if (!file_exists($jsonFile)) {
                    echo '<div class="error">Error: File hasil tidak ditemukan. Silakan lakukan pencarian terlebih dahulu.</div>';
                    echo '<a href="index.php">Kembali ke form pencarian</a>';
                    exit;
                }

                $jsonData = file_get_contents($jsonFile);
                $data = json_decode($jsonData, true);

                if (!$data || !isset($data['results'])) {
                    echo '<div class="error">Error: Data tidak valid atau kosong.</div>';
                    echo '<a href="index.php">Kembali ke form pencarian</a>';
                    exit;
                }

                $searchParams = $data['search_params'];
                $results = $data['results'];
                ?>

                <div class="info">
                    <p>Nama Penulis : <?php echo htmlspecialchars($searchParams['author']); ?></p>
                    <p>Keyword Artikel : <?php echo htmlspecialchars($searchParams['keyword']); ?></p>
                    <p>Jumlah data = <?php echo $data['total_found']; ?></p>
                </div>

                <h2>Hasil Pencarian</h2>

                <?php if (empty($results)): ?>
                    <div class="no-data">
                        <p>Tidak ada artikel yang ditemukan dengan keyword tersebut.</p>
                        <a href="index.php">Coba pencarian lain</a>
                    </div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 30%;">Judul Artikel</th>
                                <th style="width: 18%;">Penulis</th>
                                <th style="width: 10%;">Tanggal Rilis</th>
                                <th style="width: 15%;">Nama Jurnal</th>
                                <th style="width: 8%;">Jumlah Sitasi</th>
                                <th style="width: 10%;">Link Jurnal</th>
                                <th style="width: 10%;">Similarity</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($results as $article): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($article['title']); ?></td>
                                    <td><?php echo htmlspecialchars($article['authors']); ?></td>
                                    <td><?php
                                        // Tampilkan publish_date jika ada, fallback ke year
                                        $date = isset($article['publish_date']) ? $article['publish_date'] : $article['year'];
                                        echo htmlspecialchars($date);
                                        ?></td>
                                    <td><?php echo htmlspecialchars($article['journal']); ?></td>
                                    <td style="text-align: center;"><?php echo htmlspecialchars($article['citations']); ?></td>
                                    <td class="link-cell">
                                        <?php if ($article['link'] != 'N/A'): ?>
                                            <a href="<?php echo htmlspecialchars($article['link']); ?>" target="_blank">
                                                <?php echo htmlspecialchars($article['link']); ?>
                                            </a>
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($article['tfidf_similarity']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>

                <div style="margin-top: 20px;">
                    <a href="index.php">« Kembali ke Pencarian</a>
                </div>
    </div>
</body>

</html>