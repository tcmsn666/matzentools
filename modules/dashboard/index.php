<?php
$user = currentUser();
$objectStats = db()->query("SELECT status, COUNT(*) count FROM objects GROUP BY status")->fetchAll();
$activeObjects = 0; $inactiveObjects = 0;
foreach ($objectStats as $row) { if ($row['status'] === 'aktiv') $activeObjects = (int)$row['count']; else $inactiveObjects = (int)$row['count']; }
$outdated = (int)db()->query("SELECT COUNT(*) FROM object_qm_documents oqd JOIN qm_documents d ON d.id=oqd.document_id LEFT JOIN qm_document_versions cv ON cv.document_id=d.id AND cv.is_current=1 LEFT JOIN qm_document_versions av ON av.id=oqd.document_version_id WHERE d.status='aus_qm_entfernt' OR cv.change_type='aus_qm_entfernt' OR oqd.document_version_id<>cv.id")->fetchColumn();
$current = (int)db()->query("SELECT COUNT(*) FROM object_qm_documents oqd JOIN qm_documents d ON d.id=oqd.document_id JOIN qm_document_versions cv ON cv.document_id=d.id AND cv.is_current=1 WHERE d.status<>'aus_qm_entfernt' AND cv.change_type<>'aus_qm_entfernt' AND oqd.document_version_id=cv.id")->fetchColumn();
?>
<div class="main-placeholder">Hauptfenster</div>
<div class="panel">
    <h2>Willkommen, <?= e($user['display_name']) ?></h2>
    <p>Dieses System ist ein lauffähiges Grundgerüst für eine klassische Verwaltungsoberfläche unter <strong>/matzentools/</strong>.</p>
    <p>Eingeloggt als <strong><?= e($user['username']) ?></strong> mit Rolle <strong><?= e($user['role']) ?></strong>.</p>
</div>
<div class="panel">
    <h3>Aktuelle Programmänderungen</h3>
    <ul><li>Grundstruktur, Login, Rechte, Adminbereich und Modul „Objekte / QM“ wurden vorbereitet.</li><li>Weitere Module können über Modul-Key und sicheren Modulpfad ergänzt werden.</li></ul>
</div>
<div class="panel chart-box">
    <h3>Demo-Grafik</h3>
    <canvas id="dashboardChart" height="160"></canvas>
</div>
<script>
renderChart('dashboardChart','bar',['aktive Objekte','inaktive Objekte','aktuelle Zuordnungen','veraltete Zuordnungen'],[{label:'Anzahl',data:[<?= $activeObjects ?>,<?= $inactiveObjects ?>,<?= $current ?>,<?= $outdated ?>],backgroundColor:['#218838','#777','#006765','#e58900']}]);
</script>
