<?php
date_default_timezone_set('Asia/Manila');
ini_set('session.use_strict_mode', '1');
session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => !empty($_SERVER['HTTPS'])]);
session_start();

const DB_HOST = 'localhost', DB_NAME = 'byte_busters', DB_USER = 'root', DB_PASS = '';
const CATEGORIES = ['Microcontrollers', 'Sensors', 'Passives', 'Prototyping', 'Kits'];
const PICKUP_POINT = 'Byte Busters pickup counter, Davao City';
const DISCOUNT_MIN = 1000;   // orders at or above this get the discount
const DISCOUNT_RATE = 0.05;  // 5%

try {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $ex) {
    http_response_code(500);
    exit('Database connection failed. Check the settings at the top of config.php.');
}

// Pages inside /admin or /api need ../ in front of asset links
$BASE = in_array(basename(dirname($_SERVER['SCRIPT_NAME'])), ['admin', 'api']) ? '../' : '';

function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function peso($n) { return '₱' . number_format((float)$n, 2); }
function user() { return $_SESSION['user'] ?? null; }
function json_out($data, $code = 200) { http_response_code($code); echo json_encode($data); exit; }

function csrf_token() { return $_SESSION['csrf'] ??= bin2hex(random_bytes(16)); }
function check_csrf() {
    $t = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['csrf'] ?? '');
    if (!hash_equals($_SESSION['csrf'] ?? '', $t)) {
        http_response_code(419);
        exit('Your session token expired. Go back, refresh the page and try again.');
    }
}

function safe_next($n) {
    $n = (string)$n;
    if ($n === '' || str_starts_with($n, '//') || !preg_match('~^/?[\w\-/\.]+(\?[\w=&\-\.%]*)?$~', $n)) return 'index.php';
    return $n;
}
function require_login() {
    if (!user()) { header('Location: ' . ($GLOBALS['BASE']) . 'login.php?next=' . urlencode($_SERVER['REQUEST_URI'])); exit; }
}
function require_admin() {
    require_login();
    if (user()['role'] !== 'admin') { http_response_code(403); exit('Admins only.'); }
}

function cart_rows($pdo, $uid) {
    $s = $pdo->prepare('SELECT p.id, p.name, p.price, p.image, p.stock, c.qty
                        FROM cart_items c JOIN products p ON p.id = c.product_id
                        WHERE c.user_id = ? ORDER BY c.id');
    $s->execute([$uid]);
    return $s->fetchAll();
}
function cart_count($pdo) {
    if (!user()) return 0;
    $s = $pdo->prepare('SELECT COALESCE(SUM(qty),0) FROM cart_items WHERE user_id = ?');
    $s->execute([user()['id']]);
    return (int)$s->fetchColumn();
}
function totals($subtotal) {
    $d = $subtotal >= DISCOUNT_MIN ? round($subtotal * DISCOUNT_RATE, 2) : 0;
    return [(float)$subtotal, (float)$d, (float)$subtotal - $d];
}
function cart_summary($pdo, $uid) {
    $items = []; $sub = 0; $count = 0;
    foreach (cart_rows($pdo, $uid) as $r) {
        $line = $r['price'] * $r['qty'];
        $sub += $line; $count += $r['qty'];
        $items[] = ['id' => (int)$r['id'], 'qty' => (int)$r['qty'], 'line' => $line];
    }
    [$s, $d, $t] = totals($sub);
    return ['ok' => true, 'count' => $count, 'items' => $items, 'subtotal' => $s, 'discount' => $d, 'total' => $t];
}

function product_img($p, $cls = '') {
    global $BASE;
    $inner = $p['image']
        ? '<img src="' . $BASE . 'uploads/' . e($p['image']) . '" alt="' . e($p['name']) . '">'
        : '<span class="ph">' . e(mb_strtoupper(mb_substr($p['name'], 0, 2))) . '</span>';
    return '<div class="thumb t-' . (crc32($p['category']) % 4) . ' ' . $cls . '">' . $inner . '</div>';
}
function product_card($p) {
    $out = $p['stock'] < 1;
    return '<article class="card">
      <a href="product.php?id=' . (int)$p['id'] . '">' . product_img($p) . '</a>
      <div class="card-body">
        <a class="card-name" href="product.php?id=' . (int)$p['id'] . '">' . e($p['name']) . '</a>
        <p class="mono muted">' . e($p['category']) . ' · ' . ($out ? 'sold out' : (int)$p['stock'] . ' in stock') . '</p>
        <div class="card-foot"><strong>' . peso($p['price']) . '</strong>
        <button class="round" data-add="' . (int)$p['id'] . '" aria-label="Add ' . e($p['name']) . ' to bag"' . ($out ? ' disabled' : '') . '>+</button></div>
      </div></article>';
}
