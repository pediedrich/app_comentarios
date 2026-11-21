<?php

declare(strict_types=1);

final class CommentService
{
    public function __construct(private CommentRepository $repository)
    {
    }

    public function createComment(string $text): array
    {
        $text = $this->validateText($text);
        return $this->repository->create($text);
    }

    public function createReply(string $text, int $parentId): array
    {
        $text = $this->validateText($text);
        $parent = $this->repository->find($parentId);

        if ($parent === null) {
            throw new DomainException('El comentario principal no existe.');
        }

        if ($parent['parent_id'] !== null) {
            throw new DomainException('No se puede responder a otra respuesta.');
        }

        return $this->repository->create($text, $parentId);
    }

    public function deleteByModeration(int $id): bool
    {
        return $this->repository->deleteByModeration($id);
    }

    public function list(): array
    {
        return $this->repository->list();
    }

    private function validateText(string $text): string
    {
        $text = trim($text);

        if ($text === '') {
            throw new InvalidArgumentException('El comentario no puede estar vacio.');
        }

        if (function_exists('mb_strlen') && mb_strlen($text, 'UTF-8') > 500) {
            throw new InvalidArgumentException('El comentario no puede superar 500 caracteres.');
        }

        if (!function_exists('mb_strlen') && strlen($text) > 500) {
            throw new InvalidArgumentException('El comentario no puede superar 500 caracteres.');
        }

        return $text;
    }
}
