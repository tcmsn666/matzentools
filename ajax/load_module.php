<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireAjax();
requireCsrf();
$user = currentUser();
if (!$user) {
    jsonResponse(['success' => false, 'message' => 'Nicht angemeldet.'], 401);
}
$moduleKey = postString('module_key', 80);
requireModuleAccess($moduleKey);
jsonResponse(['success' => true, 'html' => renderModule($moduleKey)]);
