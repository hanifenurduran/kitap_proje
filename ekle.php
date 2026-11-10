<?php
require_once 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $baslik = $_POST['baslik'] ?? '';
    $yazar = $_POST['yazar'] ?? 'Bilinmiyor';
    $kategori = $_POST['kategori'] ?? 'Roman';
    $aciklama = $_POST['aciklama'] ?? '';
    $durum = $_POST['durum'] ?? 'Okunacak';
    $puan = $_POST['puan'] ?? 5;
    $kapak = $_POST['kapak_url'] ?? '';

    if (isset($_FILES['kapak_dosya']) && $_FILES['kapak_dosya']['error'] === UPLOAD_ERR_OK) {
        $dosyaGeciciYol = $_FILES['kapak_dosya']['tmp_name'];
        $dosyaAdi = $_FILES['kapak_dosya']['name'];
        $uzanti = strtolower(pathinfo($dosyaAdi, PATHINFO_EXTENSION));

        $izinVerilenler = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

        if (in_array($uzanti, $izinVerilenler)) {
            if (!file_exists('uploads')) {
                mkdir('uploads', 0777, true);
            }

            $yeniDosyaAdi = 'kapak_' . time() . '_' . rand(1000, 9999) . '.' . $uzanti;
            $hedefYol = 'uploads/' . $yeniDosyaAdi;

            if (move_uploaded_file($dosyaGeciciYol, $hedefYol)) {
                $kapak = $hedefYol;
            }
        }
    }

    if (!empty($baslik)) {
        kitapEkle($baslik, $yazar, $kategori, $aciklama, $durum, $puan, $kapak);
        header('Location: index.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yeni Kitap Ekle - Bougainville</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: #fce4ec; 
            padding: 15px; 
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            box-sizing: border-box;
        }
        .form-card { 
            width: 100%;
            max-width: 500px; 
            background: rgba(255, 255, 255, 0.95); 
            padding: 20px 25px; 
            border-radius: 18px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.1); 
            border: 1px solid rgba(255,255,255,0.8);
            box-sizing: border-box;
            max-height: 95vh;
            overflow-y: auto;
        }
        .form-card h2 {
            margin: 0 0 12px 0;
            color: #3e2723;
            text-align: center;
            font-size: 1.4rem;
        }
        .form-group { margin-bottom: 10px; }
        .form-group label { 
            display: block; 
            margin-bottom: 3px; 
            font-weight: 600; 
            color: #4e342e; 
            font-size: 13px;
        }
        .form-group input, .form-group select, .form-group textarea { 
            width: 100%; 
            padding: 7px 10px; 
            border-radius: 7px; 
            border: 1px solid #d7ccc8; 
            box-sizing: border-box; 
            outline: none;
            font-size: 13px;
            background: #fff;
        }
        .form-row {
            display: flex;
            gap: 10px;
        }
        .form-row .form-group {
            flex: 1;
        }
        .file-or-url-box {
            background: #f5ebe0;
            padding: 8px 10px;
            border-radius: 8px;
            border: 1px dashed #a1887f;
            margin-bottom: 10px;
        }
        .btn-submit { 
            background: linear-gradient(135deg, #8d6e63, #6d4c41); 
            color: white; 
            padding: 10px; 
            border: none; 
            border-radius: 8px; 
            width: 100%; 
            cursor: pointer; 
            font-weight: bold; 
            font-size: 14px;
            box-shadow: 0 4px 10px rgba(109, 76, 65, 0.3);
            margin-top: 5px;
        }
        .btn-submit:hover { transform: translateY(-1px); }
        .btn-back {
            display: block;
            text-align: center;
            margin-top: 8px;
            color: #795548;
            text-decoration: none;
            font-size: 12.5px;
        }
    </style>
</head>
<body>
    <div class="form-card">
        <h2>🌸 Bougainville - Yeni Kitap Ekle</h2>
        
        <form action="ekle.php" method="POST" enctype="multipart/form-data">
            
            <div class="form-group">
                <label>Kitap Adı *</label>
                <input type="text" name="baslik" required placeholder="Örn: Ruh Adam">
            </div>

            <div class="form-group">
                <label>Yazar</label>
                <input type="text" name="yazar" placeholder="Örn: Hüseyin Nihal Atsız">
            </div>

            <div class="file-or-url-box">
                <div class="form-group" style="margin-bottom: 4px;">
                    <label>🖼️ Bilgisayardan Kapak Resmi Yükle:</label>
                    <input type="file" name="kapak_dosya" accept="image/*" style="padding: 4px;">
                </div>
                <div style="text-align: center; font-size: 10px; color: #8d6e63; margin: 2px 0; font-weight: bold;">- VEYA -</div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label>🔗 İnternetten Kapak Resmi Linki (URL):</label>
                    <input type="url" name="kapak_url" placeholder="https://site.com/resim.jpg">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Kitap Türü (Kategori)</label>
                    <select name="kategori">
                        <option value="Roman">Roman</option>
                        <option value="Klasik">Klasik</option>
                        <option value="Kişisel Gelişim">Kişisel Gelişim</option>
                        <option value="Bilim Kurgu">Bilim Kurgu</option>
                        <option value="Tarih">Tarih</option>
                        <option value="Şiir">Şiir</option>
                        <option value="Felsefe">Felsefe</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Okuma Durumu</label>
                    <select name="durum">
                        <option value="Okunacak">Okunacak</option>
                        <option value="Okunuyor">Okunuyor</option>
                        <option value="Okundu">Okundu</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Değerlendirme Puanı (1 - 5 Kristal)</label>
                <select name="puan">
                    <option value="5">♦️♦️♦️♦️♦️ (5 Kristal - Mükemmel)</option>
                    <option value="4">♦️♦️♦️♦️ (4 Kristal - Çok İyi)</option>
                    <option value="3">♦️♦️♦️ (3 Kristal - Orta)</option>
                    <option value="2">♦️♦️ (2 Kristal - Zayıf)</option>
                    <option value="1">♦️ (1 Kristal - Kötü)</option>
                </select>
            </div>

            <div class="form-group">
                <label>Açıklama / Özet</label>
                <textarea name="aciklama" rows="2" placeholder="Kitap hakkında kısa özet..."></textarea>
            </div>

            <button type="submit" class="btn-submit">Kitabı Raflara Ekle 📚</button>
            <a href="index.php" class="btn-back">← Ana Sayfaya Dön</a>
        </form>
    </div>
</body>
</html>