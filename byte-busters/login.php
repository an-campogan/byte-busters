<?php
require 'config.php';
$next = safe_next($_GET['next'] ?? $_POST['next'] ?? '');
if (user()) { header('Location: ' . $next); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $s = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $s->execute([strtolower(trim($_POST['email'] ?? ''))]);
    $u = $s->fetch();
    if ($u && password_verify($_POST['password'] ?? '', $u['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = ['id' => (int)$u['id'], 'name' => $u['name'], 'role' => $u['role']];
        header('Location: ' . ($u['role'] === 'admin' && $next === 'index.php' ? 'admin/index.php' : $next));
        exit;
    }
    $error = 'Email or password is incorrect.';
}
$title = 'Log in';
require 'includes/header.php';
?>
<section class="authbox">
  <h1>Log in</h1>
  <?php if ($error): ?><p class="alert"><?= e($error) ?></p><?php endif; ?>
  <?php if (isset($_GET['next'])): ?><p class="muted">Log in to add items to your bag.</p><?php endif; ?>
  <form method="post" class="form">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <input type="hidden" name="next" value="<?= e($next) ?>">
    <label>Email <input type="email" name="email" required autocomplete="email"></label>
    <label>Password <input type="password" name="password" required autocomplete="current-password"></label>
    <button class="btn dark">Log in</button>
  </form>
  <p>New here? <a href="register.php?next=<?= urlencode($next) ?>">Create an account</a></p>
</section>
<?php require 'includes/footer.php'; ?>
