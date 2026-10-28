<?php
require 'config.php';
$next = safe_next($_GET['next'] ?? $_POST['next'] ?? '');
if (user()) { header('Location: index.php'); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $name = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $pw = $_POST['password'] ?? '';
    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $error = 'Enter your name and a valid email.';
    elseif (strlen($pw) < 8) $error = 'Use a password with at least 8 characters.';
    elseif ($pw !== ($_POST['confirm'] ?? '')) $error = 'The passwords do not match.';
    else {
        $s = $pdo->prepare('SELECT id FROM users WHERE email = ?'); $s->execute([$email]);
        if ($s->fetch()) $error = 'That email already has an account.';
        else {
            $pdo->prepare('INSERT INTO users (name,email,password_hash) VALUES (?,?,?)')
                ->execute([mb_substr($name, 0, 80), $email, password_hash($pw, PASSWORD_DEFAULT)]);
            session_regenerate_id(true);
            $_SESSION['user'] = ['id' => (int)$pdo->lastInsertId(), 'name' => $name, 'role' => 'user'];
            header('Location: ' . $next); exit;
        }
    }
}
$title = 'Create account';
require 'includes/header.php';
?>
<section class="authbox">
  <h1>Create account</h1>
  <?php if ($error): ?><p class="alert"><?= e($error) ?></p><?php endif; ?>
  <form method="post" class="form">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <input type="hidden" name="next" value="<?= e($next) ?>">
    <label>Full name <input name="name" required maxlength="80" value="<?= e($_POST['name'] ?? '') ?>"></label>
    <label>Email <input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>"></label>
    <label>Password <input type="password" name="password" required minlength="8" autocomplete="new-password"></label>
    <label>Confirm password <input type="password" name="confirm" required minlength="8" autocomplete="new-password"></label>
    <button class="btn dark">Create account</button>
  </form>
  <p>Already registered? <a href="login.php?next=<?= urlencode($next) ?>">Log in</a></p>
</section>
<?php require 'includes/footer.php'; ?>
