<?php
$title = 'Small parts, big possibilities';
require 'includes/header.php';
$popular = $pdo->query('SELECT p.* FROM products p WHERE p.stock > 0
    ORDER BY (SELECT COALESCE(SUM(qty),0) FROM order_items WHERE product_id = p.id) DESC, p.id DESC LIMIT 4')->fetchAll();
$counts = $pdo->query('SELECT category, COUNT(*) n FROM products GROUP BY category')->fetchAll(PDO::FETCH_KEY_PAIR);
?>
<section class="hero">
  <div class="hero-copy">
    <p class="mono">Electronics parts for students and hobbyists</p>
    <h1>Small parts.<br>Big possibilities.</h1>
    <p class="lede">Resistors, breadboards, Arduino boards and ESP32s with local stock, so your build does not wait on shipping from overseas.</p>
    <a class="btn coral" href="products.php">Shop components</a>
  </div>
  <div class="hero-art" aria-hidden="true">
    <span class="bubble b1">NE555</span>
    <div class="chip"><b>IC · 555</b></div>
    <span class="bubble b2">1 kΩ</span>
  </div>
</section>

<section class="block">
  <div class="block-head"><h2>Shop by category</h2></div>
  <div class="cats">
    <?php foreach (CATEGORIES as $c): ?>
      <a class="cat" href="products.php?cat=<?= urlencode($c) ?>"><strong><?= e($c) ?></strong><span class="mono muted"><?= (int)($counts[$c] ?? 0) ?> items</span></a>
    <?php endforeach; ?>
  </div>
</section>

<section class="block">
  <div class="block-head"><h2>Popular on the workbench</h2><a class="mono" href="products.php">View all</a></div>
  <div class="grid"><?php foreach ($popular as $p) echo product_card($p); ?></div>
</section>
<?php require 'includes/footer.php'; ?>
