<?php

declare(strict_types=1);

final class CommentRepository
{
    public function __construct(private PDO $db)
    {
    }

    public function create(string $text, ?int $parentId = null): array
    {
        $this->db->beginTransaction();
        try {
            $statement = $this->db->prepare(
                'INSERT INTO comments (text, parent_id) VALUES (:text, :parent_id)'
            );
            $statement->execute([
                ':text' => $text,
                ':parent_id' => $parentId,
            ]);
            $id = (int) $this->db->lastInsertId();

            $statement = $this->db->prepare(
                'SELECT id, text, parent_id, created_at
                 FROM comments
                 WHERE id = :id AND deleted_at IS NULL'
            );
            $statement->execute([':id' => $id]);
            $comment = $statement->fetch();

            $this->db->commit();
            return $comment ?: throw new RuntimeException('No fue posible confirmar la publicacion.');
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $exception;
        }
    }

    public function find(int $id): ?array
    {
        $statement = $this->db->prepare(
            'SELECT id, text, parent_id, created_at
             FROM comments
             WHERE id = :id AND deleted_at IS NULL'
        );
        $statement->execute([':id' => $id]);
        $comment = $statement->fetch();
        return $comment === false ? null : $comment;
    }

    public function countReplies(int $parentId): int
    {
        $statement = $this->db->prepare(
            'SELECT COUNT(*)
             FROM comments
             WHERE parent_id = :parent_id AND deleted_at IS NULL'
        );
        $statement->execute([':parent_id' => $parentId]);
        return (int) $statement->fetchColumn();
    }

    public function deleteByModeration(int $id): bool
    {
        $this->db->beginTransaction();
        try {
            $comment = $this->find($id);
            if ($comment === null) {
                $this->db->rollBack();
                return false;
            }

            if ($this->countReplies($id) > 0) {
                $this->db->rollBack();
                throw new DomainException('No se puede eliminar un comentario con respuestas.');
            }

            $statement = $this->db->prepare(
                'UPDATE comments SET deleted_at = CURRENT_TIMESTAMP(6) WHERE id = :id'
            );
            $statement->execute([':id' => $id]);

            $this->db->commit();
            return $statement->rowCount() === 1;
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $exception;
        }
    }

    public function list(): array
    {
        $statement = $this->db->query(
            'SELECT id, text, parent_id, created_at
             FROM comments
             WHERE deleted_at IS NULL
             ORDER BY created_at DESC, id DESC'
        );
        $comments = $statement->fetchAll();

        $topLevel = [];
        $replies = [];

        foreach ($comments as $comment) {
            $comment['id'] = (int) $comment['id'];
            $comment['parent_id'] = $comment['parent_id'] === null
                ? null
                : (int) $comment['parent_id'];

            if ($comment['parent_id'] === null) {
                $comment['replies'] = [];
                $topLevel[$comment['id']] = $comment;
            } else {
                $replies[] = $comment;
            }
        }

        foreach ($replies as $reply) {
            $parentId = $reply['parent_id'];
            if (isset($topLevel[$parentId])) {
                $topLevel[$parentId]['replies'][] = $reply;
            }
        }

        return array_values($topLevel);
    }
}
