<?php
require __DIR__ . '/db.php';
$pdo = chat_pdo();

$after = isset($_GET['after']) ? (int)$_GET['after'] : 0;

if ($after <= 0) {
  // первый заход — отдаём последние 50 сообщений
  $rows = $pdo->query('SELECT * FROM messages ORDER BY id DESC LIMIT 50')->fetchAll();
  $rows = array_reverse($rows); // по возрастанию
} else {
  // новые сообщения после известного id
  $stmt = $pdo->prepare('SELECT * FROM messages WHERE id > ? ORDER BY id ASC LIMIT 100');
  $stmt->execute([$after]);
  $rows = $stmt->fetchAll();
}

$last_id = $after;
foreach ($rows as $r) $last_id = max($last_id, (int)$r['id']);

json_out(['messages'=>$rows, 'last_id'=>$last_id]);
