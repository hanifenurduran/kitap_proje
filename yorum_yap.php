<?php
require_once 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kitapId = $_POST['kitap_id'] ?? null;
    $yorumMetni = $_POST['yorum'] ?? '';

    if ($kitapId && !empty(trim($yorumMetni))) {
        yorumEkle($kitapId, $yorumMetni);
    }
}

header('Location: index.php');
exit;
?>