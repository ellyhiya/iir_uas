<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Pencarian Artikel</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            padding: 40px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .container {
            background: white;
            padding: 40px;
            max-width: 1400px;
            margin: 0 auto;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e0e0e0;
        }

        h1 {
            font-size: 32px;
            font-weight: 700;
            color: #2d3748;
            margin: 0;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            padding: 10px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .back-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(102, 126, 234, 0.4);
        }

        .back-link::before {
            content: '←';
            margin-right: 8px;
            font-size: 18px;
        }

        .info {
            background: linear-gradient(135deg, #f6f8fb 0%, #e9ecef 100%);
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 30px;
            border-left: 4px solid #667eea;
        }

        .info p {
            margin: 12px 0;
            font-size: 15px;
            color: #4a5568;
            display: flex;
            align-items: center;
        }

        .info p strong {
            color: #2d3748;
            min-width: 180px;
            font-weight: 600;
        }

        .info-value {
            color: #667eea;
            font-weight: 600;
        }

        h2 {
            font-size: 24px;
            margin: 30px 0 20px 0;
            font-weight: 700;
            color: #2d3748;
            position: relative;
            padding-left: 15px;
        }

        h2::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 2px;
        }

        .table-container {
            overflow-x: auto;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 16px 12px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: none;
        }

        th:first-child {
            border-top-left-radius: 12px;
        }

        th:last-child {
            border-top-right-radius: 12px;
        }

        td {
            padding: 16px 12px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: top;
            font-size: 14px;
            color: #4a5568;
        }

        tbody tr {
            transition: all 0.2s ease;
        }

        tbody tr:hover {
            background-color: #f7fafc;
            transform: scale(1.01);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        .link-cell a {
            color: #667eea;
            text-decoration: none;
            word-break: break-all;
            font-weight: 500;
            transition: color 0.2s ease;
        }

        .link-cell a:hover {
            color: #764ba2;
            text-decoration: underline;
        }

        .similarity-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 13px;
            text-align: center;
        }

        .similarity-high {
            background: #d4edda;
            color: #155724;
        }

        .similarity-medium {
            background: #fff3cd;
            color: #856404;
        }

        .similarity-low {
            background: #f8d7da;
            color: #721c24;
        }

        .citation-count {
            display: inline-block;
            padding: 4px 10px;
            background: #e6f3ff;
            color: #0066cc;
            border-radius: 6px;
            font-weight: 600;
        }

        .no-data {
            text-align: center;
            padding: 60px 20px;
            color: #718096;
        }

        .no-data p {
            font-size: 18px;
            margin-bottom: 20px;
        }

        .error {
            background: linear-gradient(135deg, #fee 0%, #fdd 100%);
            color: #c62828;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            border-left: 4px solid #c62828;
            font-weight: 500;
        }

        .footer-link {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #e0e0e0;
        }

        .footer-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s ease;
        }

        .footer-link a:hover {
            color: #764ba2;
        }

        @media (max-width: 768px) {
            body {
                padding: 20px 10px;
            }

            .container {
                padding: 20px;
            }

            h1 {
                font-size: 24px;
            }

            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .info p {
                flex-direction: column;
                align-items: flex-start;
            }

            .info p strong {
                min-width: auto;
                margin-bottom: 4px;
            }

            table {
                font-size: 12px;
            }

            th,
            td {
                padding: 10px 8px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>📚 Hasil Pencarian Artikel</h1>
            <a href="index.php" class="back-link">Kembali</a>
        </div>

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
            <p><strong>👤 Nama Penulis:</strong> <span class="info-value"><?php echo htmlspecialchars($searchParams['author']); ?></span></p>
            <p><strong>🔍 Keyword Artikel:</strong> <span class="info-value"><?php echo htmlspecialchars($searchParams['keyword']); ?></span></p>
            <p><strong>📊 Jumlah Data:</strong> <span class="info-value"><?php echo $data['total_found']; ?> artikel</span></p>
        </div>

        <h2>Daftar Artikel Ditemukan</h2>

        <?php if (empty($results)): ?>
            <div class="no-data">
                <p>❌ Tidak ada artikel yang ditemukan dengan keyword tersebut.</p>
                <a href="index.php" class="back-link">Coba pencarian lain</a>
            </div>
        <?php else: ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 28%;">Judul Artikel</th>
                            <th style="width: 18%;">Penulis</th>
                            <th style="width: 10%;">Tanggal</th>
                            <th style="width: 15%;">Jurnal</th>
                            <th style="width: 8%;">Sitasi</th>
                            <th style="width: 12%;">Link</th>
                            <th style="width: 9%;">Similarity</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results as $article): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($article['title']); ?></strong></td>
                                <td><?php echo htmlspecialchars($article['authors']); ?></td>
                                <td><?php
                                    $date = isset($article['publish_date']) ? $article['publish_date'] : $article['year'];
                                    echo htmlspecialchars($date);
                                    ?></td>
                                <td><?php echo htmlspecialchars($article['journal']); ?></td>
                                <td style="text-align: center;">
                                    <span class="citation-count"><?php echo htmlspecialchars($article['citations']); ?></span>
                                </td>
                                <td class="link-cell">
                                    <?php if ($article['link'] != 'N/A'): ?>
                                        <a href="<?php echo htmlspecialchars($article['link']); ?>" target="_blank">
                                            🔗 View
                                        </a>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;">
                                    <?php
                                    $sim = floatval($article['tfidf_similarity']);
                                    $class = $sim >= 0.5 ? 'similarity-high' : ($sim >= 0.3 ? 'similarity-medium' : 'similarity-low');
                                    ?>
                                    <span class="similarity-badge <?php echo $class; ?>">
                                        <?php echo htmlspecialchars($article['tfidf_similarity']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <div class="footer-link">
            <a href="index.php">← Kembali ke Pencarian</a>
        </div>
    </div>
</body>

</html>