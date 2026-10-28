<?php
require 'config.php';
require_login();
$uid = user()['id'];
$saved = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $name = trim($_POST['name'] ?? '');
    if ($name !== '') {
        $pdo->prepare('UPDATE users SET name=?, phone=?, address=? WHERE id=?')
            ->execute([mb_substr($name, 0, 80), mb_substr(trim($_POST['phone'] ?? ''), 0, 30), mb_substr(trim($_POST['address'] ?? ''), 0, 255), $uid]);
        $_SESSION['user']['name'] = $name;
        $saved = true;
    }
}
$me = $pdo->prepare('SELECT * FROM users WHERE id = ?'); $me->execute([$uid]); $me = $me->fetch();
$os = $pdo->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC'); $os->execute([$uid]); $orders = $os->fetchAll();
$byOrder = [];
if ($orders) {
    $ids = array_column($orders, 'id');
    $q = $pdo->prepare('SELECT * FROM order_items WHERE order_id IN (' . implode(',', array_fill(0, count($ids), '?')) . ')');
    $q->execute($ids);
    foreach ($q->fetchAll() as $i) $byOrder[$i['order_id']][] = $i;
}
$title = 'My account';
require 'includes/header.php';
?>
<h1 class="page-title">My account</h1>
<div class="two-col">
  <section class="panel">
    <h2>Profile</h2>
    <?php if ($saved): ?><p class="okmsg">Profile saved.</p><?php endif; ?>
    <form method="post" class="form">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
      <label>Full name <input name="name" required maxlength="80" value="<?= e($me['name']) ?>"></label>
      <label>Email <input value="<?= e($me['email']) ?>" disabled></label>
      <label>Phone <input name="phone" maxlength="30" value="<?= e($me['phone']) ?>"></label>
      <label>Delivery address <textarea name="address" rows="3" maxlength="255"><?= e($me['address']) ?></textarea></label>
      <button class="btn dark">Save profile</button>
    </form>
  </section>
  <section>
    <h2>Order history</h2>
    <?php if (!$orders): ?>
      <div class="empty"><p>No orders yet.</p><a class="btn dark" href="products.php">Browse components</a></div>
    <?php endif; foreach ($orders as $o): ?>
      <article class="order">
        <header>
          <div><strong>BB-<?= str_pad($o['id'], 6, '0', STR_PAD_LEFT) ?></strong>
          <p class="mono muted"><?= e(date('M j, Y g:i A', strtotime($o['created_at']))) ?></p></div>
          <span class="badge s-<?= e($o['status']) ?>"><?= e(ucfirst($o['status'])) ?></span>
        </header>
        <ul>
          <?php foreach ($byOrder[$o['id']] ?? [] as $i): ?>
            <li><span><?= e($i['name']) ?> × <?= (int)$i['qty'] ?></span><span><?= peso($i['price'] * $i['qty']) ?></span></li>
          <?php endforeach; ?>
        </ul>
        <footer>
          <span class="mono muted"><?= $o['method'] === 'pickup' ? 'Pickup' : 'Delivery' ?> · <?= e(date('M j, Y', strtotime($o['eta_date']))) ?></span>
          <strong><?= peso($o['total']) ?></strong>
        </footer>
      </article>
    <?php endforeach; ?>
  </section>
</div>
<?php require 'includes/footer.php'; ?>
