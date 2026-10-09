<?php
require __DIR__ . '/config.php';
header('Content-Type: application/json; charset=utf-8');
function out($ok, $msg = '') { echo json_encode(['ok' => $ok, 'msg' => $msg], JSON_UNESCAPED_UNICODE); exit; }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') out(false, '잘못된 요청입니다.');
if (!empty($_POST['website'])) out(true);               // 스팸봇 차단
$f = function ($k, $max = 200) { return mb_substr(trim(str_replace(["\r", "\n"], ' ', $_POST[$k] ?? '')), 0, $max); };
$name = $f('name', 50); $tel = $f('tel', 30); $type = $f('type', 20);
$area = $f('area', 50); $need = $f('need', 50); $msg = $f('msg', 1000);
if ($name === '' || $tel === '') out(false, '상호/성함과 연락처를 입력해주세요.');
if (!preg_match('/^[0-9\-\s+()]{8,20}$/', $tel)) out(false, '연락처를 숫자로 정확히 입력해주세요.');
if (empty($_POST['agree'])) out(false, '개인정보 수집·이용 동의에 체크해주세요.');

$row = [date('Y-m-d H:i'), $name, $tel, $type, $area, $need, $msg, $_SERVER['REMOTE_ADDR'] ?? ''];
$new = !file_exists($DATA_FILE);
$fp = @fopen($DATA_FILE, 'a');
if (!$fp) out(false, '저장 폴더 권한을 확인해주세요. (data 폴더 707)');
flock($fp, LOCK_EX);
if ($new) { fwrite($fp, "\xEF\xBB\xBF"); fputcsv($fp, ['접수일시','상호/성함','연락처','업종','지역','필요한 것','메모','IP']); }
fputcsv($fp, $row);
flock($fp, LOCK_UN); fclose($fp);

if ($NOTIFY_EMAIL) {
  $sub = '=?UTF-8?B?' . base64_encode("[DBR 상담신청] $name / $type") . '?=';
  $body = "접수일시: {$row[0]}\n상호/성함: $name\n연락처: $tel\n업종: $type\n지역: $area\n필요한 것: $need\n메모: $msg\n";
  @mail($NOTIFY_EMAIL, $sub, $body, "Content-Type: text/plain; charset=UTF-8");
}
out(true);
