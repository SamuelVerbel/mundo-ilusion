<?php
// Respaldo de la base de datos: php cli/respaldo.php  -> guarda storage/backups/AAAA-MM-DD_HHMM.sql
// En XAMPP usa mysqldump de C:\xampp\mysql\bin (se detecta solo). Programa este script con el Programador de tareas de Windows (diario).
require dirname(__DIR__) . '/config/config.php';
$dir = ROOT_PATH . '/storage/backups'; if (!is_dir($dir)) mkdir($dir, 0775, true);
$bin = 'mysqldump';
foreach (['C:\\xampp\\mysql\\bin\\mysqldump.exe', '/opt/lampp/bin/mysqldump', '/Applications/XAMPP/xamppfiles/bin/mysqldump'] as $c) if (is_file($c)) { $bin = $c; break; }
$file = $dir . '/' . date('Y-m-d_Hi') . '.sql';
$pass = (string) env('DB_PASS', '');
$cmd = escapeshellarg($bin) . ' --host=' . escapeshellarg((string) env('DB_HOST', '127.0.0.1')) . ' --port=' . escapeshellarg((string) env('DB_PORT', '3306')) . ' --user=' . escapeshellarg((string) env('DB_USER', 'root'))
     . ($pass !== '' ? ' --password=' . escapeshellarg($pass) : '') . ' --single-transaction --routines --default-character-set=utf8mb4 --result-file=' . escapeshellarg($file) . ' ' . escapeshellarg((string) env('DB_NAME', 'mundo_ilusion')) . ' 2>&1';
exec($cmd, $out, $code);
if ($code !== 0 || !is_file($file) || filesize($file) < 500) { @unlink($file); fwrite(STDERR, "No se pudo crear el respaldo.\n" . implode("\n", $out) . "\n"); exit(1); }
// conserva solo los 14 más recientes
$all = glob($dir . '/*.sql'); sort($all); foreach (array_slice($all, 0, max(0, count($all) - 14)) as $old) @unlink($old);
echo "Respaldo creado: $file (" . round(filesize($file) / 1024) . " KB)\n";
