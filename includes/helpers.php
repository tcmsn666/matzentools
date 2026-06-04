<?php
declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function isPost(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function postString(string $key, int $max = 255): string
{
    $value = trim((string)($_POST[$key] ?? ''));
    return mb_substr($value, 0, $max);
}

function postInt(string $key, int $default = 0): int
{
    return filter_var($_POST[$key] ?? $default, FILTER_VALIDATE_INT) !== false ? (int)$_POST[$key] : $default;
}

function validDate(?string $value): ?string
{
    $value = trim((string)$value);
    if ($value === '') {
        return null;
    }
    $dt = DateTime::createFromFormat('Y-m-d', $value);
    return $dt && $dt->format('Y-m-d') === $value ? $value : null;
}

function jsonResponse(array $payload, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function requireAjax(): void
{
    if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
        jsonResponse(['success' => false, 'message' => 'Nur AJAX-Anfragen erlaubt.'], 400);
    }
}

function statusBadge(string $status): string
{
    $class = match ($status) {
        'aktiv', 'aktuell' => 'status-green',
        'inaktiv', 'ersetzt' => 'status-gray',
        'veraltet' => 'status-orange',
        'aus_qm_entfernt', 'aus QM entfernt' => 'status-red',
        default => 'status-gray',
    };
    return '<span class="badge ' . $class . '">' . e($status) . '</span>';
}
