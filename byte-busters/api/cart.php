<?php
require __DIR__ . '/../config.php';
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_out(['ok' => false, 'error' => 'POST only.'], 405);
if (!user()) json_out(['ok' => false, 'login' => true, 'error' => 'Log in to use your bag.'], 401);
check_csrf();

$in = json_decode(file_get_contents('php://input'), true) ?: [];
$act = $in['action'] ?? '';
$pid = (int)($in['product_id'] ?? 0);
$qty = max(1, (int)($in['qty'] ?? 1));
$uid = user()['id'];

if (in_array($act, ['add', 'set', 'remove'], true)) {
    $p = $pdo->prepare('SELECT id, stock FROM products WHERE id = ?');
    $p->execute([$pid]);
    $prod = $p->fetch();
    if (!$prod) json_out(['ok' => false, 'error' => 'Product not found.'], 404);

    if ($act === 'remove') {
        $pdo->prepare('DELETE FROM cart_items WHERE user_id = ? AND product_id = ?')->execute([$uid, $pid]);
    } else {
        if ($prod['stock'] < 1) json_out(['ok' => false, 'error' => 'That item is sold out.']);
        $have = 0;
        if ($act === 'add') {
            $c = $pdo->prepare('SELECT qty FROM cart_items WHERE user_id = ? AND product_id = ?');
            $c->execute([$uid, $pid]);
            $have = (int)$c->fetchColumn();
        }
        $new = min($have + $qty, (int)$prod['stock']);
        $pdo->prepare('INSERT INTO cart_items (user_id, product_id, qty) VALUES (?,?,?)
                       ON DUPLICATE KEY UPDATE qty = VALUES(qty)')->execute([$uid, $pid, $new]);
    }
}
json_out(cart_summary($pdo, $uid));
