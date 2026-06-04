<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireAjax(); requireCsrf(); requireAdmin();
$action = postString('action', 40);
try {
    if ($action === 'save') {
        $id = postInt('id'); $password = (string)($_POST['password'] ?? '');
        $data = [postString('username',80), postString('display_name',120), in_array($_POST['role'] ?? 'user',['admin','user'],true)?$_POST['role']:'user', isset($_POST['is_active'])?1:0, isset($_POST['must_change_password'])?1:0];
        if ($id > 0) {
            db()->prepare('UPDATE users SET username=?, display_name=?, role=?, is_active=?, must_change_password=?, updated_at=NOW() WHERE id=?')->execute([...$data,$id]);
            if ($password !== '') db()->prepare('UPDATE users SET password_hash=?, must_change_password=1, updated_at=NOW() WHERE id=?')->execute([password_hash($password, PASSWORD_DEFAULT),$id]);
        } else {
            db()->prepare('INSERT INTO users (username,password_hash,display_name,role,is_active,must_change_password) VALUES (?,?,?,?,?,?)')->execute([$data[0],password_hash($password ?: 'Start123!', PASSWORD_DEFAULT),$data[1],$data[2],$data[3],1]);
        }
    } elseif ($action === 'toggle') {
        db()->prepare('UPDATE users SET is_active = 1 - is_active, updated_at=NOW() WHERE id=? AND username <> "admin"')->execute([postInt('id')]);
    }
    jsonResponse(['success'=>true,'message'=>'Benutzer gespeichert.']);
} catch (Throwable $e) { error_log($e->getMessage()); jsonResponse(['success'=>false,'message'=>'Benutzer konnte nicht gespeichert werden.'],500); }
