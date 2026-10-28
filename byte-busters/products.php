<?php
$title = 'Components';
require 'includes/header.php';
$cat = $_GET['cat'] ?? '';
$q = trim($_GET['q'] ?? '');
$max = (int)($_GET['max'] ?? 0);
$sort = $_GET['sort'] ?? 'featured';
$inStock = isset($_GET['stock']);

$where = ['1']; $args = [];
if (in_array($cat, CATEGORIES, true)) { $where[] = 'category = ?'; $args[] = $cat; } else { $cat = ''; }
if ($q !== '') { $where[] = '(name LIKE ? OR description LIKE ?)'; $args[] = "%$q%"; $args[] = "%$q%"; }
if ($max > 0) { $where[] = 'price <= ?'; $args[] = $max; }
if ($inStock) { $where[] = 'stock > 0'; }
$order = ['price_asc' => 'price ASC', 'price_desc' => 'price DESC', 'newest' => 'id DESC'][$sort] ?? 'id ASC';

$s = $pdo->prepare('SELECT * FROM products WHERE ' . implode(' AND ', $where) . " ORDER BY $order");
$s->execute($args);
$items = $s->fetchAll();
$counts = $pdo->query('SELECT category, COUNT(*) n FROM products GROUP BY category')->fetchAll(PDO::FETCH_KEY_PAIR);
?>
<section class="banner sage">
  <p class="mono">Component directory</p>
  <h1>Find the exact part.</h1>
  <p class="mono muted"><?= count($items) ?> result<?= count($items) === 1 ? '' : 's' ?></p>
</section>
<div class="catalog">
  <aside class="side">
    <h2>Shop by category</h2>
    <ul class="catlist">
      <li><a href="products.php" class="<?= $cat === '' ? 'on' : '' ?>">All components (<?= array_sum($counts) ?>)</a></li>
      <?php foreach (CATEGORIES as $c): ?>
        <li><a href="products.php?cat=<?= urlencode($c) ?>" class="<?= $cat === $c ? 'on' : '' ?>"><?= e($c) ?> (<?= (int)($counts[$c] ?? 0) ?>)</a></li>
      <?php endforeach; ?>
    </ul>
    <form method="get" class="filters">
      <input type="hidden" name="cat" value="<?= e($cat) ?>">
      <label>Search <input type="search" name="q" value="<?= e($q) ?>"></label>
      <label>Max price (₱) <input type="number" name="max" min="0" step="10" value="<?= $max ?: '' ?>" placeholder="Any"></label>
      <label>Sort by
        <select name="sort">
          <option value="featured">Featured</option>
          <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Price: low to high</option>
          <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Price: high to low</option>
          <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest</option>
        </select>
      </label>
      <label class="check"><input type="checkbox" name="stock" <?= $inStock ? 'checked' : '' ?>> In stock only</label>
      <button class="btn dark">Apply filters</button>
    </form>
  </aside>
  <section>
    <?php if (!$items): ?>
      <div class="empty"><p>No parts match those filters.</p><a class="btn dark" href="products.php">Clear filters</a></div>
    <?php else: ?>
      <div class="grid three"><?php foreach ($items as $p) echo product_card($p); ?></div>
    <?php endif; ?>
  </section>
</div>
<?php require 'includes/footer.php'; ?>
