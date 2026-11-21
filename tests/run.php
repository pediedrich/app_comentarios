<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/src/Database.php';
require_once dirname(__DIR__) . '/src/CommentRepository.php';
require_once dirname(__DIR__) . '/src/CommentService.php';

$config = require dirname(__DIR__) . '/config.php';
$db = Database::connection($config['database']);
$db->exec('DELETE FROM comments WHERE parent_id IS NOT NULL');
$db->exec('DELETE FROM comments');

$service = new CommentService(new CommentRepository($db));
$failures = [];
$assertions = 0;

function check(bool $condition, string $message, array &$failures, int &$assertions): void
{
    $assertions++;
    if (!$condition) {
        $failures[] = $message;
    }
}

try {
    $first = $service->createComment('Primer comentario');
    $second = $service->createComment('Segundo comentario');
    $reply = $service->createReply('Respuesta', (int) $first['id']);

    check((int) $first['id'] > 0, 'Debe crear comentarios principales.', $failures, $assertions);
    check((int) $reply['parent_id'] === (int) $first['id'], 'La respuesta debe apuntar al principal.', $failures, $assertions);

    $listed = $service->list();
    check(count($listed) === 2, 'La consulta debe mostrar dos comentarios principales.', $failures, $assertions);
    check((int) $listed[0]['id'] === (int) $second['id'], 'Debe listar primero el comentario mas reciente.', $failures, $assertions);
    check(count($listed[1]['replies']) === 1, 'Debe mostrar la respuesta bajo su comentario principal.', $failures, $assertions);

    try {
        $service->createComment('   ');
        check(false, 'Debe rechazar texto vacio.', $failures, $assertions);
    } catch (InvalidArgumentException) {
        check(true, 'Texto vacio rechazado.', $failures, $assertions);
    }

    try {
        $service->createComment(str_repeat('a', 501));
        check(false, 'Debe rechazar mas de 500 caracteres.', $failures, $assertions);
    } catch (InvalidArgumentException) {
        check(true, 'Limite de 500 caracteres aplicado.', $failures, $assertions);
    }

    try {
        $service->createReply('Respuesta invalida', (int) $reply['id']);
        check(false, 'Debe rechazar respuesta a otra respuesta.', $failures, $assertions);
    } catch (DomainException) {
        check(true, 'Anidacion de respuesta rechazada.', $failures, $assertions);
    }

    try {
        $service->deleteByModeration((int) $first['id']);
        check(false, 'Debe rechazar baja con respuestas.', $failures, $assertions);
    } catch (DomainException) {
        check(true, 'RN-007 aplicada al moderar.', $failures, $assertions);
    }

    check(
        $service->deleteByModeration((int) $second['id']),
        'Debe permitir baja moderada sin respuestas.',
        $failures,
        $assertions
    );

    $listedAfterDeletion = $service->list();
    check(
        count($listedAfterDeletion) === 1,
        'El comentario eliminado no debe aparecer en la consulta.',
        $failures,
        $assertions
    );
} catch (Throwable $exception) {
    $failures[] = 'Error inesperado: ' . $exception->getMessage();
}

$db->exec('DELETE FROM comments WHERE parent_id IS NOT NULL');
$db->exec('DELETE FROM comments');

if ($failures !== []) {
    fwrite(STDERR, 'Fallaron ' . count($failures) . " pruebas:\n");
    foreach ($failures as $failure) {
        fwrite(STDERR, "- {$failure}\n");
    }
    exit(1);
}

echo "OK: {$assertions} pruebas de integracion completadas.\n";
