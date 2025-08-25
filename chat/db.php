<?php
// SQLite-файл будет лежать рядом: chat.sqlite
function chat_pdo() {
  $path = __DIR__ . '/chat.sqlite';
  $needInit = !file_exists($path);

  $pdo = new PDO('sqlite:' . $path, null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ]);
  // чуть меньше блокировок
  $pdo->exec('PRAGMA journal_mode=WAL; PRAGMA synchronous=NORMAL; PRAGMA busy_timeout=1500;');

  if ($needInit) {
    $pdo->exec('CREATE TABLE IF NOT EXISTS messages (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      username TEXT NOT NULL,
      text TEXT NOT NULL,
      created_at INTEGER NOT NULL
    )');
  }
  return $pdo;
}

function json_out($arr, $code=200) {
  http_response_code($code);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($arr, JSON_UNESCAPED_UNICODE);
  exit;
}
