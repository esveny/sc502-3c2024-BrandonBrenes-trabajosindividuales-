<?php

require_once 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Sesión no iniciada"]);
    exit();
}

$user_id = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            if (!isset($_GET['task_id'])) {
                http_response_code(400);
                echo json_encode(["error" => "Falta el parámetro task_id"]);
                exit();
            }
            $task_id = (int)$_GET['task_id'];
            $stmt = $pdo->prepare("SELECT * FROM comments WHERE task_id = ? ORDER BY created_at DESC");
            $stmt->execute([$task_id]);
            echo json_encode($stmt->fetchAll());
            break;

        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            if (!isset($input['task_id'], $input['content'])) {
                http_response_code(400);
                echo json_encode(["error" => "Faltan datos"]);
                exit();
            }
            $task_id = $input['task_id'];
            $content = $input['content'];
            $stmt = $pdo->prepare("INSERT INTO comments (task_id, user_id, content) VALUES (?, ?, ?)");
            $stmt->execute([$task_id, $user_id, $content]);
            echo json_encode(["success" => true, "comment_id" => $pdo->lastInsertId()]);
            break;

        case 'PUT':
            if (!isset($_GET['id'])) {
                http_response_code(400);
                echo json_encode(["error" => "Falta el parámetro id"]);
                exit();
            }
            $comment_id = (int)$_GET['id'];
            $input = json_decode(file_get_contents('php://input'), true);
            if (!isset($input['content'])) {
                http_response_code(400);
                echo json_encode(["error" => "Falta contenido"]);
                exit();
            }
            $content = $input['content'];
            $stmt = $pdo->prepare("UPDATE comments SET content = ? WHERE id = ? AND user_id = ?");
            $stmt->execute([$content, $comment_id, $user_id]);
            echo json_encode(["success" => $stmt->rowCount() > 0]);
            break;

        case 'DELETE':
            if (!isset($_GET['id'])) {
                http_response_code(400);
                echo json_encode(["error" => "Falta el parámetro id"]);
                exit();
            }
            $comment_id = (int)$_GET['id'];
            $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ? AND user_id = ?");
            $stmt->execute([$comment_id, $user_id]);
            echo json_encode(["success" => $stmt->rowCount() > 0]);
            break;

        default:
            http_response_code(405);
            echo json_encode(["error" => "Método no permitido"]);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
?>
