<?php
requireAdmin();
$users = db()->query('SELECT * FROM users ORDER BY username')->fetchAll();
$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
?>
<div class="panel"><h2>Benutzerverwaltung</h2>
<form class="ajax-form form-grid" action="ajax/admin_users.php" method="post" data-reload="admin_users">
<?= csrfField() ?><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="0">
<label>Benutzername<input name="username" required></label><label>Anzeigename<input name="display_name" required></label>
<label>Rolle<select name="role"><option value="user">user</option><option value="admin">admin</option></select></label><label>Neues Passwort<input type="password" name="password" placeholder="bei Neuanlage optional: Start123!"></label>
<label><input type="checkbox" name="is_active" checked> aktiv</label><label><input type="checkbox" name="must_change_password" checked> Passwortwechsel erzwingen</label><div><button>Benutzer anlegen</button></div>
</form></div>
<div class="panel"><h3>Benutzer</h3><table class="data-table js-data-table"><thead><tr><th>ID</th><th>Benutzername</th><th>Name</th><th>Rolle</th><th>Status</th><th>Passwortwechsel</th><th>Aktion</th></tr></thead><tbody>
<?php foreach($users as $u): ?><tr><td><?= (int)$u['id'] ?></td><td><?= e($u['username']) ?></td><td><?= e($u['display_name']) ?></td><td><?= e($u['role']) ?></td><td><?= statusBadge((int)$u['is_active']?'aktiv':'inaktiv') ?></td><td><?= (int)$u['must_change_password']?'ja':'nein' ?></td><td class="actions">
<form class="ajax-form inline-form" action="ajax/admin_users.php" data-reload="admin_users" method="post"><?= csrfField() ?><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= (int)$u['id'] ?>"><button class="secondary">aktiv/inaktiv</button></form>
</td></tr><?php endforeach; ?></tbody></table></div><script>initDataTable('.js-data-table');</script>
