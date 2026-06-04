<?php
declare(strict_types=1);

const APP_NAME = 'MatzenTools';
const BASE_PATH = '/matzentools/';
const MODULE_BASE_DIR = __DIR__ . '/../modules';

$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
session_name('MATZENTOOLSSESSID');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => BASE_PATH,
    'secure' => $secure,
    'httponly' => true,
    'samesite' => 'Lax',
]);
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/permissions.php';
require_once __DIR__ . '/modules.php';
