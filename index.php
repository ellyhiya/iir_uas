<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Google Scholar Crawler - Laragon</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background-color: #f5f5f5;
        }

        .container {
            background-color: white;
            padding: 30px;
            max-width: 600px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        h1 {
            font-size: 24px;
            margin-bottom: 30px;
            font-weight: bold;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: inline-block;
            width: 200px;
            font-weight: bold;
        }

        input[type="text"],
        input[type="number"] {
            padding: 5px 10px;
            width: 250px;
            border: 1px solid #ccc;
            border-radius: 3px;
        }

        button {
            padding: 8px 20px;
            background-color: #f0f0f0;
            border: 1px solid #999;
            border-radius: 3px;
            cursor: pointer;
            font-size: 14px;
        }

        button:hover {
            background-color: #e0e0e0;
        }

        .loading {
            display: none;
            margin-top: 20px;
            color: #666;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>PENCARIAN DATA ARTIKEL ILMIAH</h1>

        <form action="process.php" method="POST" id="searchForm">
            <div class="form-group">
                <label for="author">Input Nama Penulis :</label>
                <input type="text" id="author" name="author" required>
            </div>

            <div class="form-group">
                <label for="keyword">Input Keyword Artikel :</label>
                <input type="text" id="keyword" name="keyword" required>
            </div>

            <div class="form-group">
                <label for="jumlah">Jumlah data =</label>
                <input type="number" id="jumlah" name="jumlah" min="1" max="100" value="5" required>
            </div>

            <button type="submit">Search</button>

            <div class="loading" id="loading">
                Sedang mencari data... Mohon tunggu beberapa saat.
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