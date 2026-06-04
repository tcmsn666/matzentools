<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireAjax(); requireCsrf(); requireModuleAccess('objects');
$action=postString('action',40);
try{
 if($action==='save'){
  $id=postInt('id'); $status=in_array($_POST['status']??'aktiv',['aktiv','inaktiv'],true)?$_POST['status']:'aktiv';
  $data=[postString('object_name',160),postString('address',255),$status,validDate($_POST['start_date']??''),validDate($_POST['end_date']??''),postString('notes',1000)];
  if($id>0) db()->prepare('UPDATE objects SET object_name=?,address=?,status=?,start_date=?,end_date=?,notes=?,updated_at=NOW() WHERE id=?')->execute([...$data,$id]);
  else db()->prepare('INSERT INTO objects (object_name,address,status,start_date,end_date,notes) VALUES (?,?,?,?,?,?)')->execute($data);
 } elseif($action==='deactivate') db()->prepare("UPDATE objects SET status='inaktiv', end_date=COALESCE(end_date,CURDATE()), updated_at=NOW() WHERE id=?")->execute([postInt('id')]);
 jsonResponse(['success'=>true,'message'=>'Objekt gespeichert.']);
}catch(Throwable $e){error_log($e->getMessage());jsonResponse(['success'=>false,'message'=>'Objekt konnte nicht gespeichert werden.'],500);}
