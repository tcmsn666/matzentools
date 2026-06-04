<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/pdf.php';
requireLogin();
if (!verifyCsrf($_POST['csrf_token'] ?? null)) { http_response_code(403); echo 'Ungültiges CSRF-Token.'; exit; }
requireModuleAccess('document_control');
if (!pdfAvailable()) { http_response_code(503); echo 'FPDF ist nicht installiert. Bitte /vendor/fpdf/fpdf.php bereitstellen.'; exit; }
$objectId=postInt('object_id');
$stmt=db()->prepare("SELECT o.object_name,d.document_number,d.title,av.document_date assigned_date,av.version_label assigned_version,cv.document_date current_date,cv.version_label current_version,oqd.last_checked_at FROM object_qm_documents oqd JOIN objects o ON o.id=oqd.object_id JOIN qm_documents d ON d.id=oqd.document_id JOIN qm_document_versions av ON av.id=oqd.document_version_id LEFT JOIN qm_document_versions cv ON cv.document_id=d.id AND cv.is_current=1 WHERE oqd.object_id=? ORDER BY d.document_number");
$stmt->execute([$objectId]); $rows=$stmt->fetchAll();
$title='Objekt-Dokumentenkontrolle'; $content='';
foreach($rows as $r){ $content .= $r['object_name'].' | '.$r['document_number'].' '.$r['title'].' | Ordner: '.$r['assigned_date'].' '.$r['assigned_version'].' | QM: '.$r['current_date'].' '.$r['current_version'].' | geprüft: '.$r['last_checked_at']."\n"; }
createBasicPdf($title,$content ?: 'Keine Dokumente zugeordnet.');
