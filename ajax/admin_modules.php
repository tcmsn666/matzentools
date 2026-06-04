<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireAjax(); requireCsrf(); requireAdmin();
$id=postInt('id'); $key=postString('module_key',80); $path=postString('module_path',180);
if (!preg_match('/^[a-z0-9_\-]+$/',$key) || str_contains($path,'..') || str_starts_with($path,'/')) jsonResponse(['success'=>false,'message'=>'Ungültiger Modul-Key oder Pfad.'],400);
$data=[$key,postString('title',120),$path,isset($_POST['is_active'])?1:0];
try{ if($id>0) db()->prepare('UPDATE modules SET module_key=?, title=?, module_path=?, is_active=?, updated_at=NOW() WHERE id=?')->execute([...$data,$id]); else db()->prepare('INSERT INTO modules (module_key,title,module_path,is_active) VALUES (?,?,?,?)')->execute($data); jsonResponse(['success'=>true,'message'=>'Modul gespeichert.']); }catch(Throwable $e){error_log($e->getMessage());jsonResponse(['success'=>false,'message'=>'Modul konnte nicht gespeichert werden.'],500);}
