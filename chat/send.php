<?php
require __DIR__ . '/db.php';
session_start();

$pdo = chat_pdo();

$username = trim($_POST['username'] ?? '');
$text     = trim($_POST['text'] ?? '');

if ($username === '') $username = 'Гость';

// валидация
if (mb_strlen($username) > 20) json_out(['ok'=>false,'error'=>'Ник слишком длинный']);
if ($text === '')               json_out(['ok'=>false,'error'=>'Пустое сообщение']);
if (mb_strlen($text) > 500)     json_out(['ok'=>false,'error'=>'Слишком длинное сообщение']);

// простейший rate-limit по сессии (1 сообщение/сек)
$now = time();
$prev = $_SESSION['last_msg_ts'] ?? 0;
if ($now - $prev < 1) json_out(['ok'=>false,'error'=>'Слишком часто, подожди секунду']);
$_SESSION['last_msg_ts'] = $now;

// запись
$stmt = $pdo->prepare('INSERT INTO messages (username, text, created_at) VALUES (?,?,?)');
$stmt->execute([$username, $text, $now]);

json_out(['ok'=>true]);
