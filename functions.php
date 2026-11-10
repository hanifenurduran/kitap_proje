<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$dosyaAdi = 'kitaplar.json';

function kitaplariGetir() {
    global $dosyaAdi;
    
    if (!file_exists($dosyaAdi)) {
        $varsayilanKitaplar = [
            [
                'id' => 1,
                'baslik' => 'Ruh Adam',
                'yazar' => 'Hüseyin Nihal Atsız',
                'kategori' => 'Roman',
                'aciklama' => 'Hüseyin Nihal Atsız\'ın psikolojik tahlilleri ve tarihi unsurları harmanladığı ölümsüz eseri.',
                'durum' => 'Okundu',
                'puan' => 5,
                'kapak' => 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=300',
                'yorumlar' => ['Harika bir edebi kurgu.']
            ],
            [
                'id' => 2,
                'baslik' => 'Kürk Mantolu Madonna',
                'yazar' => 'Sabahattin Ali',
                'kategori' => 'Roman',
                'aciklama' => 'Sabahattin Ali\'nin unutulmaz aşk ve yalnızlık temalı başyapıtı.',
                'durum' => 'Okunuyor',
                'puan' => 5,
                'kapak' => 'https://images.unsplash.com/photo-1512820790803-83ca734da794?w=300',
                'yorumlar' => []
            ],
            [
                'id' => 3,
                'baslik' => 'Simyacı',
                'yazar' => 'Paulo Coelho',
                'kategori' => 'Roman',
                'aciklama' => 'Kendi kişisel menkıbesini arayan bir çobanın masalsı yolculuğu.',
                'durum' => 'Okunacak',
                'puan' => 4,
                'kapak' => 'https://images.unsplash.com/photo-1532012197267-da84d127e765?w=300',
                'yorumlar' => []
            ]
        ];
        file_put_contents($dosyaAdi, json_encode($varsayilanKitaplar, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }
    
    $icerik = file_get_contents($dosyaAdi);
    return json_decode($icerik, true) ?? [];
}

function kitaplariKaydet($kitaplarDizisi) {
    global $dosyaAdi;
    file_put_contents($dosyaAdi, json_encode($kitaplarDizisi, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
}

function guvenliCikti($veri) {
    return htmlspecialchars(trim((string)$veri), ENT_QUOTES, 'UTF-8');
}

function toplamKitapSayisi($kitaplarDizisi) {
    return count($kitaplarDizisi);
}

function durumSayisiBul($kitaplarDizisi, $durum) {
    $sayac = 0;
    foreach ($kitaplarDizisi as $kitap) {
        if (isset($kitap['durum']) && $kitap['durum'] === $durum) {
            $sayac++;
        }
    }
    return $sayac;
}

function kitapEkle($baslik, $yazar, $kategori, $aciklama, $durum = 'Okunacak', $puan = 0, $kapak = '') {
    $kitaplar = kitaplariGetir();
    $yeniId = count($kitaplar) > 0 ? max(array_column($kitaplar, 'id')) + 1 : 1;
    
    // Eğer kullanıcı kapak görseli girmemişse, popüler kitaplar sözlüğünden veya Google görsellerden otomatik seç
    if (empty(trim($kapak))) {
        $baslikKucuk = mb_strtolower(trim($baslik), 'UTF-8');
        
        // Örnek otomatik kapak eşleştirmeleri
        $hazirKapaklar = [
            'ruh adam' => 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=300',
            'kürk mantolu madonna' => 'https://images.unsplash.com/photo-1512820790803-83ca734da794?w=300',
            'simyacı' => 'https://images.unsplash.com/photo-1532012197267-da84d127e765?w=300',
            'içimizdeki şeytan' => 'https://images.unsplash.com/photo-1495640388908-05fa85288e61?w=300',
            'kuyucaklı yusuf' => 'https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?w=300',
            'suç ve ceza' => 'https://images.unsplash.com/photo-1543002588-bfa74002ed7e?w=300',
            'sefiller' => 'https://images.unsplash.com/photo-1516979187457-637abb4f9353?w=300'
        ];

        // Eğer girilen kitap listede varsa onun kapak linkini al, yoksa şık genel bir kitap kapağı ata
        if (isset($hazirKapaklar[$baslikKucuk])) {
            $kapak = $hazirKapaklar[$baslikKucuk];
        } else {
            // Google / Unsplash üzerinden rastgele şık bir kitap kapağı görseli çeker
            $kapak = 'https://images.unsplash.com/photo-1544947950-fa07a98d237f?w=300';
        }
    }

    $kitaplar[] = [
        'id' => $yeniId,
        'baslik' => htmlspecialchars($baslik),
        'yazar' => htmlspecialchars($yazar),
        'kategori' => htmlspecialchars($kategori),
        'aciklama' => htmlspecialchars($aciklama),
        'durum' => htmlspecialchars($durum),
        'puan' => (int)$puan,
        'kapak' => htmlspecialchars($kapak),
        'yorumlar' => []
    ];
    
    kitaplariKaydet($kitaplar);
}

function kitapSil($id) {
    $kitaplar = kitaplariGetir();
    $guncelKitaplar = array_filter($kitaplar, function($item) use ($id) {
        return $item['id'] != $id;
    });
    
    kitaplariKaydet(array_values($guncelKitaplar));
}

function yorumEkle($kitapId, $yorumMetni) {
    $kitaplar = kitaplariGetir();
    foreach ($kitaplar as &$kitap) {
        if ($kitap['id'] == $kitapId) {
            if (!isset($kitap['yorumlar'])) {
                $kitap['yorumlar'] = [];
            }
            $kitap['yorumlar'][] = htmlspecialchars($yorumMetni);
            break;
        }
    }
    kitaplariKaydet($kitaplar);
}
?>