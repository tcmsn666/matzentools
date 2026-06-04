<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireAjax(); requireCsrf();
$action=postString('action',40);
if($action==='mark_updated') requireModuleAccess('outdated_documents'); else requireModuleAccess('document_control');
try{
 if($action==='save_check'){
  $object=postInt('object_id'); if(!$object) jsonResponse(['success'=>false,'message'=>'Objekt erforderlich.'],400);
  db()->prepare('INSERT INTO object_document_checks (object_id,work_instruction_date,work_instruction_checked_at,gbu_date,gbu_checked_at,notes,updated_by,updated_at) VALUES (?,?,?,?,?,?,?,NOW()) ON DUPLICATE KEY UPDATE work_instruction_date=VALUES(work_instruction_date), work_instruction_checked_at=VALUES(work_instruction_checked_at), gbu_date=VALUES(gbu_date), gbu_checked_at=VALUES(gbu_checked_at), notes=VALUES(notes), updated_by=VALUES(updated_by), updated_at=NOW()')->execute([$object,validDate($_POST['work_instruction_date']??''),validDate($_POST['work_instruction_checked_at']??''),validDate($_POST['gbu_date']??''),validDate($_POST['gbu_checked_at']??''),postString('notes',1000),(int)currentUser()['id']]);
 } elseif($action==='assign_doc'){
  $object=postInt('object_id'); $version=postInt('document_version_id'); $stmt=db()->prepare('SELECT document_id FROM qm_document_versions WHERE id=?'); $stmt->execute([$version]); $doc=(int)$stmt->fetchColumn(); if(!$object||!$doc) jsonResponse(['success'=>false,'message'=>'Objekt und Version erforderlich.'],400);
  db()->prepare('INSERT INTO object_qm_documents (object_id,document_id,document_version_id,assigned_at,last_checked_at,updated_by,notes) VALUES (?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE document_version_id=VALUES(document_version_id), last_checked_at=VALUES(last_checked_at), updated_by=VALUES(updated_by), notes=VALUES(notes), updated_at=NOW()')->execute([$object,$doc,$version,validDate($_POST['assigned_at']??''),validDate($_POST['last_checked_at']??''),(int)currentUser()['id'],postString('notes',1000)]);
 } elseif($action==='mark_updated'){
  $id=postInt('id'); $stmt=db()->prepare('SELECT oqd.document_id, cv.id current_version FROM object_qm_documents oqd JOIN qm_document_versions cv ON cv.document_id=oqd.document_id AND cv.is_current=1 WHERE oqd.id=?'); $stmt->execute([$id]); $row=$stmt->fetch(); if(!$row) jsonResponse(['success'=>false,'message'=>'Zuordnung nicht gefunden.'],404);
  db()->prepare('UPDATE object_qm_documents SET document_version_id=?, last_checked_at=CURDATE(), updated_by=?, updated_at=NOW() WHERE id=?')->execute([(int)$row['current_version'],(int)currentUser()['id'],$id]);
 }
 jsonResponse(['success'=>true,'message'=>'Dokumentenkontrolle gespeichert.']);
}catch(Throwable $e){error_log($e->getMessage());jsonResponse(['success'=>false,'message'=>'Aktion konnte nicht gespeichert werden.'],500);}
