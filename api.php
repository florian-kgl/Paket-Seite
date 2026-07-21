<?php
require_once 'config.php';

header('Content-Type: application/json');

$pdo = getDBConnection();
$method = $_SERVER['REQUEST_METHOD'];

// GET - Alle Pakete mit Items laden
if ($method === 'GET') {
    try {
        $stmt = $pdo->query("
            SELECT p.id, p.name, 
                   JSON_ARRAYAGG(
                       JSON_OBJECT(
                           'id', i.id,
                           'name', i.name,
                           'packed', i.packed
                       )
                   ) as items
            FROM packages p
            LEFT JOIN items i ON p.id = i.package_id
            GROUP BY p.id, p.name
            ORDER BY p.created_at DESC
        ");
        
        $packages = [];
        while ($row = $stmt->fetch()) {
            $packages[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'items' => $row['items'] ? json_decode($row['items'], true) : []
            ];
        }
        
        echo json_encode(['packages' => $packages]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

// POST - Alle Daten speichern (kompletter Sync)
elseif ($method === 'POST') {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['packages']) || !is_array($input['packages'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Ungültiges Format']);
            exit;
        }
        
        $pdo->beginTransaction();
        
        // Alle existierenden Daten löschen
        $pdo->exec("DELETE FROM items");
        $pdo->exec("DELETE FROM packages");
        
        // Neue Daten einfügen
        foreach ($input['packages'] as $pkg) {
            $pkgId = $pkg['id'];
            $pkgName = $pkg['name'];
            
            $stmt = $pdo->prepare("INSERT INTO packages (id, name) VALUES (?, ?)");
            $stmt->execute([$pkgId, $pkgName]);
            
            if (isset($pkg['items']) && is_array($pkg['items'])) {
                foreach ($pkg['items'] as $item) {
                    $itemId = $item['id'];
                    $itemName = $item['name'];
                    $itemPacked = isset($item['packed']) ? (int)$item['packed'] : 0;
                    
                    $stmt = $pdo->prepare("INSERT INTO items (id, package_id, name, packed) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$itemId, $pkgId, $itemName, $itemPacked]);
                }
            }
        }
        
        $pdo->commit();
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

else {
    http_response_code(405);
    echo json_encode(['error' => 'Methode nicht erlaubt']);
}
?>
