<?php

require 'db.php';

function crearTarea($user_id, $title, $description, $due_date)
{
    global $pdo;
    try {
        $sql = "INSERT INTO tasks (user_id, title, description, due_date) values (:user_id, :title, :description, :due_date)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'user_id' => $user_id,
            'title' => $title,
            'description' => $description,
            'due_date' => $due_date
        ]);
        return $pdo->lastInsertId();
    } catch (Exception $e) {
        logError("Error creando tarea: " . $e->getMessage());
        return 0;
    }
}

function editarTarea($id, $title, $description, $due_date)
{
    global $pdo;
    try {
        $sql = "UPDATE tasks set title = :title, description = :description, due_date = :due_date where id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'title' => $title,
            'description' => $description,
            'due_date' => $due_date,
            'id' => $id
        ]);
        return $stmt->rowCount() > 0;
    } catch (Exception $e) {
        logError($e->getMessage());
        return false;
    }
}

function obtenerTareasPorUsuario($user_id)
{
    global $pdo;
    try {
        $sql = "SELECT t.id, t.user_id, t.title, t.description, t.due_date,
                       IFNULL(
                           (SELECT JSON_ARRAYAGG(JSON_OBJECT('id', c.id, 'description', c.comment))
                            FROM comments c
                            WHERE c.task_id = t.id),
                           '[]'
                       ) AS comments
                FROM tasks t
                WHERE t.user_id = :user_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['user_id' => $user_id]);
        $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Decodificar el campo comments para asegurarnos de que sea un array válido
        foreach ($tasks as &$task) {
            $task['comments'] = json_decode($task['comments'], true);
        }

        return $tasks;
    } catch (Exception $e) {
        logError("Error al obtener tareas: " . $e->getMessage());
        return [];
    }
}

function eliminarTarea($id)
{
    global $pdo;
    try {
        $sql = "DELETE FROM tasks WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() > 0;
    } catch (Exception $e) {
        logError("Error al eliminar la tarea: " . $e->getMessage());
        return false;
    }
}

function crearComentario($task_id, $comment)
{
    global $pdo;
    try {
        $sql = "INSERT INTO comments (task_id, comment) VALUES (:task_id, :comment)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'task_id' => $task_id,
            'comment' => $comment
        ]);
        return $pdo->lastInsertId();
    } catch (Exception $e) {
        logError("Error creando comentario: " . $e->getMessage());
        return 0;
    }
}

function obtenerComentariosPorTarea($task_id)
{
    global $pdo;
    try {
        $sql = "SELECT * FROM comments WHERE task_id = :task_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['task_id' => $task_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        logError("Error al obtener comentarios: " . $e->getMessage());
        return [];
    }
}

function editarComentario($id, $comment)
{
    global $pdo;
    try {
        $sql = "UPDATE comments SET comment = :comment WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'comment' => $comment,
            'id' => $id
        ]);
        return $stmt->rowCount() > 0;
    } catch (Exception $e) {
        logError("Error al editar comentario: " . $e->getMessage());
        return false;
    }
}

function eliminarComentario($id)
{
    global $pdo;
    try {
        $sql = "DELETE FROM comments WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() > 0;
    } catch (Exception $e) {
        logError("Error al eliminar comentario: " . $e->getMessage());
        return false;
    }
}

$method = $_SERVER['REQUEST_METHOD'];
header('Content-Type: application/json');

function getJsonInput()
{
    return json_decode(file_get_contents("php://input"), true);
}

session_start();
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    switch ($method) {
        case 'GET':
            if (isset($_GET['task_id'])) {
                $task_id = (int)$_GET['task_id'];
                $comentarios = obtenerComentariosPorTarea($task_id);
                echo json_encode($comentarios);
            } else {
                $tareas = obtenerTareasPorUsuario($user_id);
                echo json_encode($tareas);
            }
            break;

        case 'POST':
            $input = getJsonInput();
            if (isset($input['task_id'], $input['comment'])) {
                $comment_id = crearComentario($input['task_id'], $input['comment']);
                echo json_encode(['success' => $comment_id > 0, 'comment_id' => $comment_id]);
            } elseif (isset($input['title'], $input['description'], $input['due_date'])) {
                $task_id = crearTarea($user_id, $input['title'], $input['description'], $input['due_date']);
                echo json_encode(['success' => $task_id > 0, 'task_id' => $task_id]);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Datos insuficientes']);
            }
            break;

        case 'PUT':
            $input = getJsonInput();
            if (isset($_GET['comment_id'], $input['comment'])) {
                $success = editarComentario((int)$_GET['comment_id'], $input['comment']);
                echo json_encode(['success' => $success]);
            } elseif (isset($_GET['id'], $input['title'], $input['description'], $input['due_date'])) {
                $success = editarTarea((int)$_GET['id'], $input['title'], $input['description'], $input['due_date']);
                echo json_encode(['success' => $success]);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Datos insuficientes']);
            }
            break;

        case 'DELETE':
            if (isset($_GET['comment_id'])) {
                $success = eliminarComentario((int)$_GET['comment_id']);
                echo json_encode(['success' => $success]);
            } elseif (isset($_GET['id'])) {
                $success = eliminarTarea((int)$_GET['id']);
                echo json_encode(['success' => $success]);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Datos insuficientes']);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            break;
    }
} else {
    http_response_code(401);
    echo json_encode(['error' => 'Sesión no activa']);
}
