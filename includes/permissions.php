<?php
declare(strict_types=1);

function menuTreeForUser(array $user): array
{
    $sql = "SELECT mi.* FROM menu_items mi
            WHERE mi.is_active = 1
              AND (:is_admin_1 = 1 OR mi.admin_only = 0)
              AND (:is_admin_2 = 1 OR EXISTS (
                  SELECT 1 FROM user_menu_permissions ump
                  WHERE ump.menu_item_id = mi.id AND ump.user_id = :user_id AND ump.can_access = 1
              ))
            ORDER BY COALESCE(mi.parent_id, mi.id), mi.parent_id IS NOT NULL, mi.sort_order, mi.title";

    $isAdmin = $user['role'] === 'admin' ? 1 : 0;

    $stmt = db()->prepare($sql);
    $stmt->execute([
        'is_admin_1' => $isAdmin,
        'is_admin_2' => $isAdmin,
        'user_id' => (int)$user['id'],
    ]);

    $items = $stmt->fetchAll();
    $tree = [];

    foreach ($items as $item) {
        if ($item['parent_id'] === null) {
            $item['children'] = [];
            $tree[$item['id']] = $item;
        }
    }

    foreach ($items as $item) {
        if ($item['parent_id'] !== null && isset($tree[$item['parent_id']])) {
            $tree[$item['parent_id']]['children'][] = $item;
        }
    }

    return array_values($tree);
}

function userCanAccessModule(array $user, string $moduleKey): bool
{
    if ($user['role'] === 'admin') {
        return true;
    }
    $stmt = db()->prepare("SELECT COUNT(*) FROM menu_items mi
        JOIN user_menu_permissions ump ON ump.menu_item_id = mi.id AND ump.user_id = ? AND ump.can_access = 1
        WHERE mi.module_key = ? AND mi.is_active = 1 AND mi.admin_only = 0");
    $stmt->execute([(int)$user['id'], $moduleKey]);
    return (int)$stmt->fetchColumn() > 0;
}

function requireModuleAccess(string $moduleKey): array
{
    $user = currentUser();
    if (!$user) {
        jsonResponse(['success' => false, 'message' => 'Nicht angemeldet.'], 401);
    }
    if (!userCanAccessModule($user, $moduleKey)) {
        jsonResponse(['success' => false, 'message' => 'Keine Berechtigung für dieses Modul.'], 403);
    }
    return $user;
}
