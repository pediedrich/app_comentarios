<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/src/Database.php';
require_once __DIR__ . '/src/CommentRepository.php';
require_once __DIR__ . '/src/CommentService.php';

function respond(int $status, array $payload): never
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $config = require __DIR__ . '/config.php';
    $db = Database::connection($config['database']);
    $service = new CommentService(new CommentRepository($db));

    $action = $_GET['action'] ?? '';

    if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'list') {
        respond(200, ['comments' => $service->list()]);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'comments') {
        $body = json_decode(file_get_contents('php://input'), true);
        if (!is_array($body)) {
            respond(400, ['error' => 'Se esperaba un cuerpo JSON valido.']);
        }

        $text = isset($body['text']) && is_string($body['text']) ? $body['text'] : '';
        $comment = $service->createComment($text);
        respond(201, ['comment' => $comment]);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'replies') {
        $body = json_decode(file_get_contents('php://input'), true);
        if (!is_array($body)) {
            respond(400, ['error' => 'Se esperaba un cuerpo JSON valido.']);
        }

        $text = isset($body['text']) && is_string($body['text']) ? $body['text'] : '';
        $parentId = isset($body['parent_id']) ? (int) $body['parent_id'] : 0;
        if ($parentId <= 0) {
            respond(400, ['error' => 'Se requiere un parent_id valido.']);
        }

        $reply = $service->createReply($text, $parentId);
        respond(201, ['comment' => $reply]);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'moderation-delete') {
        $body = json_decode(file_get_contents('php://input'), true);
        if (!is_array($body)) {
            respond(400, ['error' => 'Se esperaba un cuerpo JSON valido.']);
        }

        $id = isset($body['id']) ? (int) $body['id'] : 0;
        if ($id <= 0) {
            respond(400, ['error' => 'Se requiere un id valido.']);
        }

        $deleted = $service->deleteByModeration($id);
        respond($deleted ? 200 : 404, [
            'deleted' => $deleted,
        ]);
    }

    respond(404, ['error' => 'Accion no encontrada.']);
} catch (InvalidArgumentException | DomainException $exception) {
    respond(422, ['error' => $exception->getMessage()]);
} catch (Throwable $exception) {
    respond(500, ['error' => 'Error interno del servidor.', 'detail' => $exception->getMessage()]);
}
