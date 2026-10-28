<?php require_once __DIR__ . '/../config.php'; $u = user(); ?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf" content="<?= e(csrf_token()) ?>">
<title><?= e($title ?? 'Byte Busters') ?> · Byte Busters</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Mono&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= $BASE ?>assets/style.css">
</head>
<body>
<header class="site-header">
  <div class="wrap bar">
    <a class="logo" href="<?= $BASE ?>index.php">byte<span>.</span>busters</a>
    <nav class="nav" aria-label="Main">
      <a href="<?= $BASE ?>products.php">Components</a>
      <a href="<?= $BASE ?>products.php?cat=Kits">Project kits</a>
      <?php if ($u): ?><a href="<?= $BASE ?>account.php">Account</a><?php endif; ?>
      <?php if ($u && $u['role'] === 'admin'): ?><a href="<?= $BASE ?>admin/index.php">Admin</a><?php endif; ?>
      <?php if ($u): ?>
        <form method="post" action="<?= $BASE ?>logout.php" class="inline"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><button class="linkbtn">Log out</button></form>
      <?php else: ?>
        <a href="<?= $BASE ?>login.php">Log in</a>
      <?php endif; ?>
    </nav>
    <form class="search" action="<?= $BASE ?>products.php" role="search">
      <input type="search" name="q" placeholder="Search parts" aria-label="Search parts" value="<?= e($_GET['q'] ?? '') ?>">
    </form>
    <a class="bag" href="<?= $BASE ?>cart.php">Bag <span id="bagCount"><?= cart_count($pdo) ?></span></a>
  </div>
</header>
<main class="wrap main">
