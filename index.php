<?php
require_once __DIR__ . '/includes/bootstrap.php';
requireLogin();
$user = currentUser();
$menu = menuTreeForUser($user);
?>
<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MatzenTools</title>
    <meta name="csrf-token" content="<?= e(csrfToken()) ?>">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="<?= BASE_PATH ?>assets/css/style.css">
</head>
<body>
<div class="app-shell">
    <header class="top-header">
        <div class="logo-placeholder" aria-label="Logo links">LOGO</div>
        <h1>MatzenTools</h1>
        <div class="logo-placeholder" aria-label="Logo rechts">LOGO</div>
    </header>
    <div class="body-row">
        <aside class="sidebar">
            <div class="sidebar-title">Startseite</div>
            <nav class="menu-tree">
                <?php foreach ($menu as $parent): ?>
                    <div class="menu-group">
                        <button class="menu-parent" type="button"><?= e($parent['title']) ?> <span>›</span></button>
                        <div class="submenu">
                            <?php if (!empty($parent['module_key'])): ?>
                                <a href="#" class="module-link" data-module="<?= e($parent['module_key']) ?>"><?= e($parent['title']) ?></a>
                            <?php endif; ?>
                            <?php foreach ($parent['children'] as $child): ?>
                                <a href="#" class="module-link" data-module="<?= e($child['module_key']) ?>"><?= e($child['title']) ?></a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </nav>
            <a class="logout-link" href="<?= BASE_PATH ?>logout.php">⏻ logout</a>
        </aside>
        <main class="content-wrap">
            <div class="change-bar">Aktuelle Programmänderungen</div>
            <?php if ((int)$user['must_change_password'] === 1): ?>
                <div class="password-warning">Bitte ändern Sie das Demo-Passwort nach dem ersten Login.</div>
            <?php endif; ?>
            <section id="main-content" class="main-content" data-default-module="dashboard">
                <div class="loading">Dashboard wird geladen …</div>
            </section>
        </main>
    </div>
    <footer class="footer-bar">© MatzenTools <?= date('Y') ?></footer>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="<?= BASE_PATH ?>assets/js/app.js"></script>
</body>
</html>
