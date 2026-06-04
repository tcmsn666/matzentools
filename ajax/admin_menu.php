<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireAjax(); requireCsrf(); requireAdmin();
$id=postInt('id'); $parent=postInt('parent_id'); $parent=$parent>0?$parent:null; $module=postString('module_key',80) ?: null;
$data=[$parent,postString('title',120),$module,postInt('sort_order'),postString('icon',40),isset($_POST['is_active'])?1:0,isset($_POST['admin_only'])?1:0];
try{ if($id>0) db()->prepare('UPDATE menu_items SET parent_id=?, title=?, module_key=?, sort_order=?, icon=?, is_active=?, admin_only=?, updated_at=NOW() WHERE id=?')->execute([...$data,$id]); else db()->prepare('INSERT INTO menu_items (parent_id,title,module_key,sort_order,icon,is_active,admin_only) VALUES (?,?,?,?,?,?,?)')->execute($data); jsonResponse(['success'=>true,'message'=>'Menü gespeichert.']); }catch(Throwable $e){error_log($e->getMessage());jsonResponse(['success'=>false,'message'=>'Menüpunkt konnte nicht gespeichert werden.'],500);}
