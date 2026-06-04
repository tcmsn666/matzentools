<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireAjax(); requireCsrf(); requireModuleAccess('qm_documents');
$action=postString('action',40);
try{
 if($action==='save_doc'){
  $id=postInt('id'); $status=in_array($_POST['status']??'aktiv',['aktiv','ersetzt','aus_qm_entfernt'],true)?$_POST['status']:'aktiv'; $data=[postString('document_number',50),postString('title',180),postString('document_type',80),$status,postString('notes',1000)];
  if($id>0) db()->prepare('UPDATE qm_documents SET document_number=?,title=?,document_type=?,status=?,notes=?,updated_at=NOW() WHERE id=?')->execute([...$data,$id]); else db()->prepare('INSERT INTO qm_documents (document_number,title,document_type,status,notes) VALUES (?,?,?,?,?)')->execute($data);
 } elseif($action==='add_version'){
  $doc=postInt('document_id'); $date=validDate($_POST['document_date']??''); if(!$doc||!$date) jsonResponse(['success'=>false,'message'=>'Dokument und Datum sind erforderlich.'],400);
  $change=in_array($_POST['change_type']??'aktualisiert',['neu_erstellt','aktualisiert','aus_qm_entfernt'],true)?$_POST['change_type']:'aktualisiert';
  db()->beginTransaction(); db()->prepare('UPDATE qm_document_versions SET is_current=0, updated_at=NOW() WHERE document_id=?')->execute([$doc]); db()->prepare('INSERT INTO qm_document_versions (document_id,document_date,version_label,change_type,is_current,notes) VALUES (?,?,?,?,1,?)')->execute([$doc,$date,postString('version_label',100),$change,postString('notes',1000)]); db()->commit();
 }
 jsonResponse(['success'=>true,'message'=>'QM-Dokument gespeichert.']);
}catch(Throwable $e){ if(db()->inTransaction()) db()->rollBack(); error_log($e->getMessage()); jsonResponse(['success'=>false,'message'=>'QM-Dokument konnte nicht gespeichert werden.'],500);}
