<?php
// Run once in the browser after importing schema.sql, then DELETE this file.
require __DIR__ . '/config.php';
$has = $pdo->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn();
if ($has) exit('An admin already exists. Delete install.php.');
$pw = bin2hex(random_bytes(5));
$pdo->prepare("INSERT INTO users (name,email,password_hash,role) VALUES ('Admin','admin@bytebusters.local',?, 'admin')")
    ->execute([password_hash($pw, PASSWORD_DEFAULT)]);
echo '<p>Admin created.<br>Email: <b>admin@bytebusters.local</b><br>Password: <b>' . $pw . '</b></p><p>Save this now, then delete install.php.</p>';
