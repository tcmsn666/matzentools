<?php
declare(strict_types=1);

function moduleByKey(string $moduleKey): ?array
{
    if (!preg_match('/^[a-z0-9_\-]+$/', $moduleKey)) {
        return null;
    }
    $stmt = db()->prepare('SELECT * FROM modules WHERE module_key = ? AND is_active = 1');
    $stmt->execute([$moduleKey]);
    $module = $stmt->fetch();
    return $module ?: null;
}

function safeModuleFile(array $module): ?string
{
    $path = str_replace('\\', '/', (string)$module['module_path']);
    if ($path === '' || str_contains($path, '..') || str_starts_with($path, '/')) {
        return null;
    }
    $full = realpath(MODULE_BASE_DIR . '/' . $path);
    $base = realpath(MODULE_BASE_DIR);
    if (!$full || !$base || !str_starts_with($full, $base . DIRECTORY_SEPARATOR) || !is_file($full)) {
        return null;
    }
    return $full;
}

function renderModule(string $moduleKey): string
{
    $module = moduleByKey($moduleKey);
    if (!$module) {
        return '<div class="panel error">Modul nicht gefunden oder inaktiv.</div>';
    }
    $file = safeModuleFile($module);
    if (!$file) {
        return '<div class="panel error">Modulpfad ist ungültig.</div>';
    }
    ob_start();
    include $file;
    return (string)ob_get_clean();
}
