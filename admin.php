<?php
require __DIR__ . '/config.php';
session_start();
if (isset($_POST['pw'])) { if (hash_equals($ADMIN_PASSWORD, $_POST['pw'])) $_SESSION['dbr'] = 1; }
if (isset($_GET['logout'])) { session_destroy(); header('Location: admin.php'); exit; }
$in = !empty($_SESSION['dbr']);
if ($in && isset($_GET['csv']) && file_exists($DATA_FILE)) {
  header('Content-Type: text/csv; charset=utf-8');
  header('Content-Disposition: attachment; filename="leads_' . date('Ymd') . '.csv"');
  readfile($DATA_FILE); exit;
}
$rows = [];
if ($in && file_exists($DATA_FILE) && ($fp = fopen($DATA_FILE, 'r'))) {
  while (($r = fgetcsv($fp)) !== false) $rows[] = $r;
  fclose($fp); $head = array_shift($rows); $head[0] = preg_replace('/^\xEF\xBB\xBF/', '', $head[0]); $rows = array_reverse($rows);
}
$h = fn($s) => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
?><!doctype html><html lang="ko"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex"><title>상담 접수 목록</title>
<style>body{font-family:"Malgun Gothic",sans-serif;margin:0;padding:20px;background:#f3f4f1;color:#16202e}
table{border-collapse:collapse;width:100%;background:#fff;font-size:14px}th,td{border:1px solid #d9dcd6;padding:8px;text-align:left;vertical-align:top}
th{background:#1f3a5a;color:#fff}.box{overflow-x:auto}a,button{color:#1f3a5a}input,button{padding:10px;font-size:16px}</style></head><body>
<?php if (!$in): ?>
<form method="post"><h2>상담 접수 목록</h2><input type="password" name="pw" placeholder="비밀번호"> <button>보기</button></form>
<?php else: ?>
<h2>상담 접수 목록 (<?= count($rows) ?>건)</h2>
<p><a href="?csv=1">엑셀(CSV) 다운로드</a> · <a href="?logout=1">로그아웃</a></p>
<div class="box"><table><tr><?php foreach ($head ?? [] as $c) echo '<th>' . $h($c) . '</th>'; ?></tr>
<?php foreach ($rows as $r) { echo '<tr>'; foreach ($r as $c) echo '<td>' . $h($c) . '</td>'; echo '</tr>'; } ?>
</table></div><?php endif; ?></body></html>
