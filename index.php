<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Google Scholar Crawler - Laragon</title>
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
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .container {
            background: white;
            padding: 50px;
            max-width: 700px;
            width: 100%;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: fadeInUp 0.6s ease;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        h1 {
            font-size: 32px;
            margin-bottom: 15px;
            font-weight: 700;
            color: #2d3748;
            text-align: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .subtitle {
            text-align: center;
            color: #718096;
            margin-bottom: 40px;
            font-size: 16px;
        }

        .form-group {
            margin-bottom: 30px;
        }

        label {
            display: block;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 10px;
            font-size: 15px;
        }

        label::before {
            content: '';
            display: inline-block;
            width: 6px;
            height: 6px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            margin-right: 8px;
            vertical-align: middle;
        }

        input[type="text"],
        input[type="number"] {
            padding: 14px 18px;
            width: 100%;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 15px;
            font-family: inherit;
            transition: all 0.3s ease;
            background: #f7fafc;
        }

        input[type="text"]:focus,
        input[type="number"]:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }

        input[type="text"]::placeholder {
            color: #a0aec0;
        }

        button {
            width: 100%;
            padding: 16px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
            margin-top: 10px;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
        }

        button:active {
            transform: translateY(0);
        }

        .loading {
            display: none;
            margin-top: 20px;
            padding: 20px;
            background: linear-gradient(135deg, #f6f8fb 0%, #e9ecef 100%);
            border-radius: 10px;
            text-align: center;
            color: #4a5568;
            font-weight: 500;
            border-left: 4px solid #667eea;
        }

        .loading::before {
            content: '⏳';
            font-size: 24px;
            display: block;
            margin-bottom: 10px;
            animation: rotate 2s linear infinite;
        }

        @keyframes rotate {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        .input-hint {
            font-size: 13px;
            color: #718096;
            margin-top: 6px;
            display: block;
        }

        .icon-input-group {
            position: relative;
        }

        .icon-input-group::before {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 18px;
            z-index: 1;
        }

        .icon-input-group.author::before {
            content: '👤';
        }

        .icon-input-group.keyword::before {
            content: '🔍';
        }

        .icon-input-group.number::before {
            content: '📊';
        }

        .icon-input-group input {
            padding-left: 50px;
        }

        @media (max-width: 768px) {
            body {
                padding: 20px 15px;
            }

            .container {
                padding: 30px 25px;
            }

            h1 {
                font-size: 26px;
            }

            .subtitle {
                font-size: 14px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>📚 PENCARIAN ARTIKEL ILMIAH</h1>
        <p class="subtitle">Cari artikel ilmiah dari Google Scholar berdasarkan penulis dan keyword</p>

        <form action="process.php" method="POST" id="searchForm">
            <div class="form-group">
                <label for="author">Nama Penulis</label>
                <div class="icon-input-group author">
                    <input type="text" id="author" name="author" placeholder="Contoh: John Doe" required>
                </div>
                <span class="input-hint">Masukkan nama lengkap penulis yang ingin dicari</span>
            </div>

            <div class="form-group">
                <label for="keyword">Keyword Artikel</label>
                <div class="icon-input-group keyword">
                    <input type="text" id="keyword" name="keyword" placeholder="Contoh: machine learning" required>
                </div>
                <span class="input-hint">Kata kunci topik artikel yang ingin dicari</span>
            </div>

            <div class="form-group">
                <label for="jumlah">Jumlah Data</label>
                <div class="icon-input-group number">
                    <input type="number" id="jumlah" name="jumlah" min="1" max="100" value="5" placeholder="5" required>
                </div>
                <span class="input-hint">Maksimal 100 artikel (default: 5)</span>
            </div>

            <button type="submit">🔍 Mulai Pencarian</button>

            <div class="loading" id="loading">
                <strong>Sedang mencari data...</strong><br>
                Mohon tunggu beberapa saat, proses ini memerlukan waktu.
            </div>
        </form>
    </div>

    <script>
        document.getElementById('searchForm').addEventListener('submit', function() {
            document.getElementById('loading').style.display = 'block';
        });
    </script>
</body>

</html>