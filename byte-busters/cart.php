<?php
$title = 'Your bag';
require 'includes/header.php';
require_login();
$rows = cart_rows($pdo, user()['id']);
?>
<h1 class="page-title">Your bag</h1>
<?php if (!$rows): ?>
  <div class="empty"><p>Your bag is empty. Pick a few parts to get started.</p><a class="btn dark" href="products.php">Browse components</a></div>
<?php else: $sub = 0; foreach ($rows as $r) $sub += $r['price'] * $r['qty']; [$s, $d, $t] = totals($sub); ?>
<div class="cart">
  <div class="cart-list">
    <div class="cart-head mono muted"><span>Product</span><span>Unit price</span><span>Quantity</span><span>Subtotal</span><span></span></div>
    <?php foreach ($rows as $r): ?>
    <div class="cart-row" data-row="<?= (int)$r['id'] ?>">
      <div class="who">
        <a href="product.php?id=<?= (int)$r['id'] ?>"><?= product_img($r, 'sm') ?></a>
        <div><a class="card-name" href="product.php?id=<?= (int)$r['id'] ?>"><?= e($r['name']) ?></a>
        <p class="mono muted"><?= (int)$r['stock'] ?> in stock</p></div>
      </div>
      <div class="unit"><?= peso($r['price']) ?></div>
      <div class="stepper">
        <button type="button" class="qty-btn" data-step="-1" aria-label="Decrease quantity">−</button>
        <input class="qty-input" type="number" min="1" max="<?= (int)$r['stock'] ?>" value="<?= (int)$r['qty'] ?>" aria-label="Quantity for <?= e($r['name']) ?>">
        <button type="button" class="qty-btn" data-step="1" aria-label="Increase quantity">+</button>
      </div>
      <div class="line" data-line><?= peso($r['price'] * $r['qty']) ?></div>
      <button class="linkbtn danger" data-remove>Remove</button>
    </div>
    <?php endforeach; ?>
  </div>
  <aside class="summary">
    <h2>Order summary</h2>
    <div class="sum-row"><span>Subtotal</span><span id="sumSub"><?= peso($s) ?></span></div>
    <div class="sum-row"><span>Discount <small class="muted">(5% off ₱1,000+)</small></span><span id="sumDisc">−<?= peso($d) ?></span></div>
    <div class="sum-row total"><span>Total</span><span id="sumTotal"><?= peso($t) ?></span></div>
    <label>Get it by
      <select id="method"><option value="delivery">Delivery (about 3 days)</option><option value="pickup">Pickup (ready next day)</option></select>
    </label>
    <button class="btn coral wide" id="checkout">Proceed to checkout</button>
  </aside>
</div>
<?php endif; ?>
<?php require 'includes/footer.php'; ?>
