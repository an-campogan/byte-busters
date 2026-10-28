<?php
require 'includes/header.php';
$s = $pdo->prepare('SELECT * FROM products WHERE id = ?');
$s->execute([(int)($_GET['id'] ?? 0)]);
$p = $s->fetch();
if (!$p) { http_response_code(404); $title = 'Not found'; echo '<div class="empty"><p>That product does not exist.</p><a class="btn dark" href="products.php">Back to components</a></div>'; require 'includes/footer.php'; exit; }
$title = $p['name'];
$out = $p['stock'] < 1;
?>
<a class="mono back" href="products.php">← All components</a>
<section class="detail">
  <?= product_img($p, 'big') ?>
  <div>
    <p class="mono muted"><?= e($p['category']) ?></p>
    <h1><?= e($p['name']) ?></h1>
    <p class="price"><?= peso($p['price']) ?></p>
    <p class="lede"><?= nl2br(e($p['description'])) ?></p>
    <p class="mono <?= $out ? 'bad' : 'muted' ?>"><?= $out ? 'Sold out' : (int)$p['stock'] . ' in stock' ?></p>
    <?php if (!$out): ?>
    <div class="buy-row">
      <div class="stepper" data-stepper>
        <button type="button" class="qty-btn" data-step="-1" aria-label="Decrease quantity">−</button>
        <input id="qty" class="qty-input" type="number" min="1" max="<?= (int)$p['stock'] ?>" value="1" aria-label="Quantity">
        <button type="button" class="qty-btn" data-step="1" aria-label="Increase quantity">+</button>
      </div>
      <button class="btn dark" data-add="<?= (int)$p['id'] ?>" data-qty-from="#qty">Add to bag</button>
      <button class="btn coral" data-buy="<?= (int)$p['id'] ?>" data-qty-from="#qty">Buy now</button>
    </div>
    <?php if (!user()): ?><p class="mono muted">You can browse freely. Log in to add items to your bag.</p><?php endif; ?>
    <?php endif; ?>
  </div>
</section>
<?php require 'includes/footer.php'; ?>
