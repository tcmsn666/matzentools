<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireAjax(); requireCsrf(); requireAdmin();
$userId=postInt('user_id'); $menuIds=array_map('intval', $_POST['menu_ids'] ?? []);
try{ db()->beginTransaction(); db()->prepare('DELETE FROM user_menu_permissions WHERE user_id=?')->execute([$userId]); $stmt=db()->prepare('INSERT INTO user_menu_permissions (user_id,menu_item_id,can_access) VALUES (?,?,1)'); foreach($menuIds as $mid){$stmt->execute([$userId,$mid]);} db()->commit(); jsonResponse(['success'=>true,'message'=>'Rechte gespeichert.']); }catch(Throwable $e){ if(db()->inTransaction()) db()->rollBack(); error_log($e->getMessage()); jsonResponse(['success'=>false,'message'=>'Rechte konnten nicht gespeichert werden.'],500);}
