# Google Scholar Crawler dengan PHP dan Python Selenium

Aplikasi web untuk crawling data artikel ilmiah dari Google Scholar berdasarkan nama penulis dan keyword.

## 📋 Requirements

### PHP

- PHP 7.4 atau lebih tinggi (Sudah terinstall di Laragon)
- Apache Web Server (Sudah terinstall di Laragon)

### Python

- Python 3.8 atau lebih tinggi
- Google Chrome Browser

## 🚀 Instalasi

### 1. Setup Laragon

**Project ini sudah berada di folder Laragon:**

```
c:\laragon\www\iir_uas
```

**Pastikan Laragon sudah running:**

- Buka Laragon
- Klik "Start All" untuk menjalankan Apache dan MySQL

### 2. Install Python Dependencies

Buka Terminal di Laragon (klik kanan icon Laragon > Terminal) atau PowerShell, lalu jalankan:

```bash
cd c:\laragon\www\iir_uas
pip install -r requirements.txt
```

### 3. Pastikan Chrome Browser Terinstall

Script akan otomatis mendownload ChromeDriver yang sesuai menggunakan webdriver-manager.

## 📝 Cara Penggunaan

### Menjalankan Aplikasi

**Di Laragon:**

Setelah Laragon running, akses aplikasi melalui browser:

```
http://localhost/iir_uas
```

atau

```
http://iir-uas.test
```

(tergantung konfigurasi Laragon Anda)

### Akses Aplikasi

1. Buka browser dan akses: `http://localhost/iir_uas`
2. Isi form dengan:
   - **Nama Penulis**: Nama author yang ingin dicari (contoh: "Joko Siswantoro")
   - **Keyword Artikel**: Kata kunci untuk pencocokan (contoh: "optimization")
   - **Jumlah Data**: Berapa artikel yang ingin diambil (contoh: 10)
3. Klik tombol **Search**
4. Tunggu proses crawling selesai (bisa memakan waktu beberapa menit)
5. Hasil akan ditampilkan dalam bentuk tabel dengan kolom **Keyword Match** yang menunjukkan apakah artikel mengandung keyword atau tidak

## 🔍 Cara Kerja Crawling

1. **Search Author**: Script akan mencari nama penulis di Google Scholar menggunakan pencarian biasa
   - URL: `https://scholar.google.com/scholar?q=nama+penulis`
2. **Masuk ke Profile**: Otomatis masuk ke profile penulis yang ditemukan (paling atas)
   - Contoh: `https://scholar.google.com/citations?user=aexhi0oAAAAJ`
3. **Ambil Artikel Teratas**: Mengambil N artikel teratas dari profile (sesuai jumlah yang diminta)

4. **Masuk ke Detail Setiap Artikel**:
   - Script akan membuka halaman detail setiap artikel satu per satu
   - Contoh URL: `https://scholar.google.com/citations?view_op=view_citation&...`
   - Mengambil:
     - **Link Jurnal**: Link langsung ke artikel/paper (XPath: `//*[@id="gsc_oci_title_gg"]/div/a`)
     - **Tanggal Terbit**: Tanggal publikasi yang lebih detail dari halaman detail
5. **Keyword Matching**: Setiap artikel akan dicek apakah judulnya mengandung keyword yang dicari

6. **Hasil**: Semua artikel ditampilkan dengan:
   - Judul
   - Penulis
   - Tanggal Terbit (dari halaman detail)
   - Nama Jurnal
   - Jumlah Sitasi
   - **Keyword Match** (Yes/No)
   - Link Jurnal (link langsung ke paper)

## 📁 Struktur File

```
iir_uas/
├── index.php              # Halaman form input
├── process.php            # Proses PHP yang memanggil Python script
├── result.php             # Halaman untuk menampilkan hasil
├── scholar_crawler.py     # Script Python untuk crawling
├── requirements.txt       # Dependencies Python
├── results.json          # File hasil crawling (generated)
└── README.md             # Dokumentasi
```

## 🔧 Troubleshooting

### Error: Python not found

Pastikan Python sudah terinstall dan ditambahkan ke PATH. Cek dengan:

```bash
python --version
```

Jika belum terinstall:

1. Download Python dari https://www.python.org/downloads/
2. Install dengan mencentang "Add Python to PATH"
3. Restart Terminal/PowerShell

### Error: Apache tidak running

Pastikan Apache di Laragon sudah running:

- Buka Laragon
- Klik "Start All"
- Cek status Apache (harus berwarna hijau)

### Error: Chrome driver issues

Script menggunakan webdriver-manager yang akan otomatis mendownload ChromeDriver. Pastikan Chrome browser terinstall.

### Error: No articles found

- Pastikan nama penulis ditulis dengan benar
- Gunakan nama lengkap penulis (minimal 2 kata)
- Coba dengan nama penulis yang lebih spesifik

### Keyword tidak match

- Keyword digunakan untuk **pencocokan saja**, bukan untuk filtering
- Semua artikel tetap ditampilkan dengan kolom "Keyword Match" (Yes/No)
- Keyword akan dicocokkan dengan judul artikel (tidak case-sensitive)

### Crawling terlalu lama

Google Scholar kadang membatasi request. Tunggu beberapa saat atau gunakan VPN.

## ⚠️ Catatan Penting

- Crawling mungkin memakan waktu tergantung jumlah data yang diminta
- Google Scholar mungkin membatasi jumlah request jika terlalu sering
- Gunakan dengan bijak dan sesuai terms of service Google Scholar
- Hasil crawling disimpan di file `results.json` yang akan di-overwrite setiap pencarian baru

## 📊 Format Data Hasil

Data disimpan dalam format JSON dengan struktur:

```json
{
  "search_params": {
    "author": "Nama Penulis",
    "keyword": "keyword",
    "max_results": 10,
    "timestamp": "2025-11-26 10:30:00"
  },
  "total_found": 10,
  "results": [
    {
      "no": 1,
      "title": "Judul Artikel",
      "authors": "Author 1, Author 2",
      "journal": "Nama Jurnal",
      "year": "2020",
      "citations": "150",
      "link": "https://...",
      "keyword_match": "Yes"
    }
  ]
}
```

**Kolom `keyword_match`:**

- `"Yes"` = Judul artikel mengandung keyword yang dicari
- `"No"` = Judul artikel tidak mengandung keyword

## 👨‍💻 Author

Dibuat untuk keperluan UAS IIR
