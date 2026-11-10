<?php
require_once 'functions.php';

// KİTAP SİLME (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['islem']) && $_POST['islem'] === 'sil') {
    $silinecekId = $_POST['id'] ?? null;
    if ($silinecekId) {
        kitapSil($silinecekId);
    }
    header('Location: index.php');
    exit;
}

$tumKitaplar = kitaplariGetir();

// Raflara Göre Kitapları Ayırıyoruz
$okunduRaf = array_filter($tumKitaplar, fn($k) => $k['durum'] === 'Okundu');
$okunuyorRaf = array_filter($tumKitaplar, fn($k) => $k['durum'] === 'Okunuyor');
$okunacakRaf = array_filter($tumKitaplar, fn($k) => $k['durum'] === 'Okunacak');

// JavaScript tarafında rastgele seçim yapabilmek için JSON formatına çeviriyoruz
$okunacakJson = json_encode(array_values($okunacakRaf), JSON_UNESCAPED_UNICODE);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bougainville - 3D İnteraktif Kitap Rafları</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        :root { --bg-custom: #fce4ec; }

        body {
            background-color: var(--bg-custom);
            transition: background-color 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px 0;
            overflow-x: hidden;
        }

        /* -------------------------------------------------------------
           ANIMASYON TANIMLAMALARI (KEYFRAMES)
        ------------------------------------------------------------- */
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

        @keyframes spinMagic {
            0% { transform: rotate(0deg) scale(1); }
            50% { transform: rotate(180deg) scale(1.15); }
            100% { transform: rotate(360deg) scale(1); }
        }

        .page-header, .theme-bar, .magic-box, .shelf-container {
            animation: fadeInUp 0.8s cubic-bezier(0.25, 1, 0.5, 1) forwards;
        }
        .shelf-container:nth-child(1) { animation-delay: 0.1s; }
        .shelf-container:nth-child(2) { animation-delay: 0.2s; }
        .shelf-container:nth-child(3) { animation-delay: 0.3s; }

        /* TEMA BARI */
        .theme-bar {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            padding: 12px 25px;
            border-radius: 50px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 8px 32px rgba(0,0,0,0.06);
            margin-bottom: 25px;
            border: 1px solid rgba(255,255,255,0.6);
        }

        /* BAŞLIK VE LOGO */
        .page-header { text-align: center; margin-bottom: 30px; }
        .page-header h1 {
            font-size: 3rem;
            color: #3e2723;
            margin: 10px 0 5px 0;
            letter-spacing: 2px;
            font-family: 'Georgia', serif;
        }
        .header-logo-text {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 8px 28px;
            transition: transform 0.3s ease;
        }
        .header-logo-text:hover {
            transform: scale(1.05);
        }

        /* SİHİRLİ ÖNERİ KUTUSU */
        .magic-box {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            border: 2px dashed #8d6e63;
            border-radius: 20px;
            padding: 22px;
            text-align: center;
            margin-bottom: 35px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.04);
            transition: all 0.3s ease;
        }
        .magic-box:hover {
            border-color: #d4af37;
        }
        .btn-magic {
            background: linear-gradient(135deg, #d4af37, #aa771c);
            color: white;
            border: none;
            padding: 12px 28px;
            border-radius: 30px;
            font-weight: bold;
            font-size: 15px;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(212,175,55,0.4);
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-magic:hover { 
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 8px 25px rgba(212,175,55,0.6);
        }
        .btn-magic.spinning .icon-spin {
            animation: spinMagic 0.6s linear infinite;
        }
        #onerilenKitapSonuc {
            margin-top: 15px;
            font-weight: bold;
            color: #3e2723;
            font-size: 16px;
            min-height: 24px;
            transition: all 0.3s ease;
        }

        /* 3D RAF KAPLAMA KUTUSU */
        .shelf-container {
            background: rgba(255, 255, 255, 0.45);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    padding: 15px 20px; /* Eskisi 25px idi */
    margin-bottom: 25px; /* Eskisi 40px idi */
    box-shadow: 0 8px 25px rgba(0,0,0,0.04);
    border: 1px solid rgba(255,255,255,0.6);
    transition: box-shadow 0.3s ease;
        }
        .shelf-container:hover {
            box-shadow: 0 12px 30px rgba(0,0,0,0.07);
        }

        .shelf-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid rgba(0,0,0,0.05);
        }

        .shelf-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: #3e2723;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .books-on-shelf {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
            gap: 25px;
            padding-bottom: 15px;
            perspective: 1000px;
        }

        /* AHŞAP RAF TABANI */
        .wooden-shelf-base {
            height: 14px; /* Eskisi 20px idi */
    background: linear-gradient(to bottom, #d7ccc8, #a1887f);
    border-radius: 4px;
    box-shadow: 0 6px 12px rgba(0,0,0,0.12);
    margin-top: 5px;
    position: relative;
        }
        .wooden-shelf-base::after {
            content: '';
            position: absolute;
            top: 100%;
            left: 3%; right: 3%;
            height: 10px;
            background: rgba(0,0,0,0.1);
            filter: blur(5px);
        }

        /* 3D ETKİLEŞİMLİ KİTAP KARTI */
        .book-item-3d {
            background: #ffffff;
    border-radius: 12px;
    padding: 8px 10px; /* Eskisi 12px idi */
    box-shadow: 0 6px 16px rgba(0,0,0,0.06);
    transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transform-style: preserve-3d;
    position: relative;
        }

        .book-item-3d:hover {
            transform: translateY(-12px) rotateX(4deg) rotateY(-4deg) scale(1.02);
            box-shadow: 0 20px 35px rgba(141, 110, 99, 0.25);
            border-color: rgba(141, 110, 99, 0.3);
        }

        .book-cover {
           width: 100%;
    height: 140px;          /* İdeal kapak yüksekliği */
    object-fit: contain;    /* Resmi bozup esnetmeden kutunun içine TAM SIĞDIRIR */
    background-color: #f7f3f0; /* Transparan/farklı oranlı resimler için şık zemin */
    border-radius: 8px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    margin-bottom: 6px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .book-item-3d:hover .book-cover {
            transform: scale(1.03);
            box-shadow: 0 8px 20px rgba(0,0,0,0.3);
        }

        .book-title {
            font-weight: bold;
            font-size: 14px;
            color: #3e2723;
            margin-bottom: 3px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .book-author { font-size: 12px; color: #795548; margin-bottom: 5px; }

        .badge-kategori {
            display: inline-block;
            background: #f5ebe0;
            color: #5d4037;
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 5px;
            transition: background 0.3s, color 0.3s;
        }
        .book-item-3d:hover .badge-kategori {
            background: #8d6e63;
            color: #fff;
        }

        /* PEMBE KRİSTAL PUANLAMA STİLİ */
        .crystal-rating {
            display: flex;
            gap: 4px;
            align-items: center;
            margin-bottom: 6px;
            transition: transform 0.2s ease;
        }
        .book-item-3d:hover .crystal-rating {
            transform: scale(1.08);
            transform-origin: left center;
        }
        .crystal-icon {
            width: 45px;
            height: 45px;
            object-fit: contain;
            transition: all 0.2s ease;
            mix-blend-mode: multiply;
        }
        .crystal-icon.empty {
            opacity: 0.25;
            filter: grayscale(80%);
            mix-blend-mode: multiply;
        }

        .yorum-kutusu {
            background: #fafafa;
            padding: 6px;
            border-radius: 8px;
            font-size: 11px;
            margin-top: 6px;
            border: 1px solid #efebe9;
        }
        .yorum-formu input { width: 65%; padding: 4px; font-size: 11px; border: 1px solid #d7ccc8; border-radius: 4px; }
        .yorum-formu button { padding: 4px 6px; font-size: 11px; background: #8d6e63; color: white; border: none; border-radius: 4px; cursor: pointer; transition: background 0.2s; }
        .yorum-formu button:hover { background: #6d4c41; }

        .btn-primary { 
            background: linear-gradient(135deg, #8d6e63, #6d4c41); 
            border: none; 
            color: white; 
            padding: 10px 22px; 
            border-radius: 12px; 
            text-decoration: none; 
            font-weight: bold; 
            box-shadow: 0 4px 12px rgba(109, 76, 65, 0.25);
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(109, 76, 65, 0.4);
        }

        .btn-sil-post { 
            color: #d32f2f; 
            background: #ffebee; 
            border: none; 
            font-size: 11px; 
            padding: 4px 10px; 
            border-radius: 6px; 
            cursor: pointer; 
            font-weight: bold; 
            transition: all 0.2s ease;
        }
        .btn-sil-post:hover {
            background: #d32f2f;
            color: white;
            transform: scale(1.05);
        }
    </style>
</head>
<body>
    <div style="max-width: 1150px; margin: 0 auto; padding: 0 15px;">
        
        <!-- ÜST TEMA BARI -->
        <div class="theme-bar">
            <div style="font-weight: bold; color: #3e2723; display: flex; align-items: center; gap: 8px;">
                🎨 <span>Arayüz Özelleştirme</span>
            </div>
            <div style="display: flex; gap: 12px; align-items: center;">
                <span style="font-size: 13.5px; color: #4e342e;">Arka Plan Rengi:</span>
                <input type="color" id="bgColorPicker" value="#fce4ec" style="border:none; cursor:pointer; width:28px; height:28px; border-radius:50%;">
                <div class="color-preset" style="width:22px;height:22px;border-radius:50%;background:#fce4ec;cursor:pointer;box-shadow:0 2px 6px rgba(0,0,0,0.15);" onclick="changeBg('#fce4ec')"></div>
                <div class="color-preset" style="width:22px;height:22px;border-radius:50%;background:#e3f2fd;cursor:pointer;box-shadow:0 2px 6px rgba(0,0,0,0.15);" onclick="changeBg('#e3f2fd')"></div>
                <div class="color-preset" style="width:22px;height:22px;border-radius:50%;background:#e8f5e9;cursor:pointer;box-shadow:0 2px 6px rgba(0,0,0,0.15);" onclick="changeBg('#e8f5e9')"></div>
            </div>
        </div>

        <!-- BAŞLIK VE LOGO -->
        <header class="page-header">
            <div class="header-logo-text">
                <img src="assets/logo-removebg-preview.png" alt="Bougainville Logo" style="max-height: 160px; object-fit: contain;">
            </div>
            <p style="color: #5d4037; font-size: 1.05rem; margin-top: 8px;">Okuduğun ve okuyacağın eserlerin 3D kişisel kitap rafları.</p>
        </header>

        <!-- SİHİRLİ ÖNERİ KUTUSU -->
        <div class="magic-box">
            <h3 style="margin-top: 0; color: #3e2723; font-family: 'Georgia', serif; font-size: 1.3rem;">✨ Bougainville Çarkı: Bugün Ne Okusam?</h3>
            <p style="font-size: 13.5px; color: #5d4037; margin-bottom: 16px;">Kararsız mı kaldın? Okunacak listenden senin için sihirli bir kitap seçelim!</p>
            <button class="btn-magic" id="btnMagic" onclick="rastgeleKitapSec()">
                <span class="icon-spin">🎲</span>
                <span>Sihirli Kitap Çarkını Çevir!</span>
            </button>
            <div id="onerilenKitapSonuc"></div>
        </div>

        <!-- NAVİGASYON -->
        <div style="margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center;">
            <div style="font-weight: bold; color: #3e2723; font-size: 15px;">
                📚 Toplam Kayıt: <strong><?php echo toplamKitapSayisi($tumKitaplar); ?></strong> Kitap
            </div>
            <a href="ekle.php" class="btn-primary">+ Yeni Kitap Ekle</a>
        </div>

        <!-- 1. RAF: OKUNUYOR -->
        <div class="shelf-container">
            <div class="shelf-header">
                <div class="shelf-title">📖 Okunuyor (<?php echo count($okunuyorRaf); ?>)</div>
            </div>
            <div class="books-on-shelf">
                <?php foreach ($okunuyorRaf as $kitap): ?>
                    <div class="book-item-3d">
                        <div>
                            <img src="<?php echo guvenliCikti($kitap['kapak'] ?? ''); ?>" class="book-cover" alt="Kapak">
                            <span class="badge-kategori"><?php echo guvenliCikti($kitap['kategori'] ?? 'Genel'); ?></span>
                            <div class="book-title"><?php echo guvenliCikti($kitap['baslik']); ?></div>
                            <div class="book-author"><?php echo guvenliCikti($kitap['yazar'] ?? 'Bilinmiyor'); ?></div>
                            
                            <!-- PEMBE KRİSTAL PUANLAMA -->
                            <div class="crystal-rating">
                                <?php 
                                $puan = isset($kitap['puan']) ? (int)$kitap['puan'] : 0;
                                for($i=1; $i<=5; $i++) {
                                    $sinif = $i <= $puan ? 'crystal-icon' : 'crystal-icon empty';
                                    echo '<img src="assets/kristal-removebg-preview.png" class="' . $sinif . '" alt="kristal">';
                                }
                                ?>
                            </div>
                        </div>

                        <div style="display: flex; justify-content: flex-end; align-items: center; margin-top: 8px; border-top: 1px solid #f5ebe0; padding-top: 6px;">
                            <form action="index.php" method="POST" style="margin:0;" onsubmit="return confirm('Silmek istiyor musunuz?');">
                                <input type="hidden" name="islem" value="sil">
                                <input type="hidden" name="id" value="<?php echo $kitap['id']; ?>">
                                <button type="submit" class="btn-sil-post">🗑️ Sil</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if(empty($okunuyorRaf)): ?>
                    <p style="color: #8d6e63; font-style: italic;">Şu an okuduğunuz bir kitap bulunmuyor.</p>
                <?php endif; ?>
            </div>
            <div class="wooden-shelf-base"></div>
        </div>

        <!-- 2. RAF: OKUNDU -->
        <div class="shelf-container">
            <div class="shelf-header">
                <div class="shelf-title">✨ Okundu (<?php echo count($okunduRaf); ?>)</div>
            </div>
            <div class="books-on-shelf">
                <?php foreach ($okunduRaf as $kitap): ?>
                    <div class="book-item-3d">
                        <div>
                            <img src="<?php echo guvenliCikti($kitap['kapak'] ?? ''); ?>" class="book-cover" alt="Kapak">
                            <span class="badge-kategori"><?php echo guvenliCikti($kitap['kategori'] ?? 'Genel'); ?></span>
                            <div class="book-title"><?php echo guvenliCikti($kitap['baslik']); ?></div>
                            <div class="book-author"><?php echo guvenliCikti($kitap['yazar'] ?? 'Bilinmiyor'); ?></div>
                            
                            <!-- PEMBE KRİSTAL PUANLAMA -->
                            <div class="crystal-rating">
                                <?php 
                                $puan = isset($kitap['puan']) ? (int)$kitap['puan'] : 0;
                                for($i=1; $i<=5; $i++) {
                                    $sinif = $i <= $puan ? 'crystal-icon' : 'crystal-icon empty';
                                    echo '<img src="assets/kristal-removebg-preview.png" class="' . $sinif . '" alt="kristal">';
                                }
                                ?>
                            </div>

                            <div class="yorum-kutusu">
                                <strong>💬 Yorumlar:</strong>
                                <?php if(!empty($kitap['yorumlar'])): ?>
                                    <ul style="padding-left: 12px; margin: 2px 0;">
                                        <?php foreach($kitap['yorumlar'] as $y): ?>
                                            <li><?php echo guvenliCikti($y); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                                <form action="yorum_yap.php" method="POST" class="yorum-formu" style="display:flex; gap:4px; margin-top:4px;">
                                    <input type="hidden" name="kitap_id" value="<?php echo $kitap['id']; ?>">
                                    <input type="text" name="yorum" placeholder="Yorum yaz..." required>
                                    <button type="submit">Ekle</button>
                                </form>
                            </div>
                        </div>

                        <div style="display: flex; justify-content: flex-end; align-items: center; margin-top: 8px; border-top: 1px solid #f5ebe0; padding-top: 6px;">
                            <form action="index.php" method="POST" style="margin:0;" onsubmit="return confirm('Silmek istiyor musunuz?');">
                                <input type="hidden" name="islem" value="sil">
                                <input type="hidden" name="id" value="<?php echo $kitap['id']; ?>">
                                <button type="submit" class="btn-sil-post">🗑️ Sil</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if(empty($okunduRaf)): ?>
                    <p style="color: #8d6e63; font-style: italic;">Henüz okuduğunuz bir kitap bulunmuyor.</p>
                <?php endif; ?>
            </div>
            <div class="wooden-shelf-base"></div>
        </div>

        <!-- 3. RAF: OKUNACAK -->
        <div class="shelf-container">
            <div class="shelf-header">
                <div class="shelf-title">🔮 Okunacak (<?php echo count($okunacakRaf); ?>)</div>
            </div>
            <div class="books-on-shelf">
                <?php foreach ($okunacakRaf as $kitap): ?>
                    <div class="book-item-3d">
                        <div>
                            <img src="<?php echo guvenliCikti($kitap['kapak'] ?? ''); ?>" class="book-cover" alt="Kapak">
                            <span class="badge-kategori"><?php echo guvenliCikti($kitap['kategori'] ?? 'Genel'); ?></span>
                            <div class="book-title"><?php echo guvenliCikti($kitap['baslik']); ?></div>
                            <div class="book-author"><?php echo guvenliCikti($kitap['yazar'] ?? 'Bilinmiyor'); ?></div>
                            
                            <!-- PEMBE KRİSTAL PUANLAMA -->
                            <div class="crystal-rating">
                                <?php 
                                $puan = isset($kitap['puan']) ? (int)$kitap['puan'] : 0;
                                for($i=1; $i<=5; $i++) {
                                    $sinif = $i <= $puan ? 'crystal-icon' : 'crystal-icon empty';
                                    echo '<img src="assets/kristal-removebg-preview.png" class="' . $sinif . '" alt="kristal">';
                                }
                                ?>
                            </div>
                        </div>

                        <div style="display: flex; justify-content: flex-end; align-items: center; margin-top: 8px; border-top: 1px solid #f5ebe0; padding-top: 6px;">
                            <form action="index.php" method="POST" style="margin:0;" onsubmit="return confirm('Silmek istiyor musunuz?');">
                                <input type="hidden" name="islem" value="sil">
                                <input type="hidden" name="id" value="<?php echo $kitap['id']; ?>">
                                <button type="submit" class="btn-sil-post">🗑️ Sil</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if(empty($okunacakRaf)): ?>
                    <p style="color: #8d6e63; font-style: italic;">Okunacak kitap listeniz boş.</p>
                <?php endif; ?>
            </div>
            <div class="wooden-shelf-base"></div>
        </div>

    </div>

    <script>
        const okunacakKitaplar = <?php echo $okunacakJson; ?>;

        function rastgeleKitapSec() {
            const btn = document.getElementById('btnMagic');
            const sonucDiv = document.getElementById('onerilenKitapSonuc');

            if (okunacakKitaplar.length === 0) {
                sonucDiv.style.opacity = '0';
                setTimeout(() => {
                    sonucDiv.innerHTML = "⚠️ Okunacak rafınızda hiç kitap yok! Önce kitap eklemelisiniz.";
                    sonucDiv.style.opacity = '1';
                }, 200);
                return;
            }

            btn.classList.add('spinning');
            sonucDiv.style.opacity = '0.4';
            sonucDiv.innerHTML = "🔮 Bougainville çarkı dönüyor...";

            setTimeout(() => {
                btn.classList.remove('spinning');
                const rastgeleIndex = Math.floor(Math.random() * okunacakKitaplar.length);
                const secilen = okunacakKitaplar[rastgeleIndex];
                
                sonucDiv.style.opacity = '1';
                sonucDiv.innerHTML = `🎉 Tavsiye Edilen Eser: <span style="color:#d4af37; font-size:18px; font-weight:bold;">“${secilen.baslik}”</span> (${secilen.yazar ?? 'Bilinmiyor'})`;
            }, 700);
        }

        const picker = document.getElementById('bgColorPicker');
        window.addEventListener('DOMContentLoaded', () => {
            const savedBg = localStorage.getItem('user_bg_color');
            if (savedBg) {
                document.body.style.backgroundColor = savedBg;
                picker.value = savedBg;
            }
        });
        picker.addEventListener('input', (e) => changeBg(e.target.value));
        function changeBg(color) {
            document.body.style.backgroundColor = color;
            picker.value = color;
            localStorage.setItem('user_bg_color', color);
        }
    </script>
</body>
</html>