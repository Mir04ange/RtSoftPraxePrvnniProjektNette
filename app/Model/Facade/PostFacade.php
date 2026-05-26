<?php

declare(strict_types=1);

namespace App\Model\Facade;

use App\Model\CommentRepository;
use App\Model\DTO\CommentDto;
use App\Model\DTO\PostDto;
use App\Model\Mapper\CommentMapper;
use App\Model\Mapper\PostMapper;
use App\Model\PostRepository;
use Nette\Database\Explorer;
use RuntimeException;

/**
 * Fasáda pro správu příspěvků a komentářů.
 * Sjednocuje logiku z více repozitářů a zajišťuje transakční zpracování.
 * Vrací čisté DTO objekty místo databázových řádků.
 */
final class PostFacade
{
    public function __construct(
        private PostRepository $postRepository,
        private CommentRepository $commentRepository,
        private Explorer $database,
        private PostMapper $postMapper,
        private CommentMapper $commentMapper,
    ) {
    }

    /**
     * Vyhledá veřejné příspěvky, volitelně s limitem.
     * @return list<PostDto>
     */
    public function findPublicPosts(?int $limit = null): array
    {
        $selection = $this->postRepository->findPublicPosts();

        if ($limit !== null && $limit > 0) {
            $selection->limit($limit);
        }

        return $this->postMapper->mapMany($selection);
    }

    /**
     * Získá detail jednoho příspěvku podle ID.
     */
    public function getPost(int $id): ?PostDto
    {
        $post = $this->postRepository->getById($id);

        return $post === null ? null : $this->postMapper->map($post);
    }

    /**
     * Vyhledá všechny komentáře k danému příspěvku.
     * @return list<CommentDto>
     */
    public function findCommentsByPostId(int $postId): array
    {
        return $this->commentMapper->mapMany($this->commentRepository->findByPostId($postId));
    }

    /**
     * Získá detail jednoho komentáře podle ID.
     */
    public function getComment(int $id): ?CommentDto
    {
        $comment = $this->commentRepository->getById($id);

        return $comment === null ? null : $this->commentMapper->map($comment);
    }

    /**
     * Uloží příspěvek (vytvoří nový nebo aktualizuje stávající).
     * @param array<string, mixed> $data
     */
    public function savePost(?int $id, array $data): PostDto
    {
        return $this->postMapper->map($this->postRepository->save($id, $data));
    }

    /**
     * Uloží komentář k příspěvku. Provádí validaci existence příspěvku i komentáře.
     * @param array<string, mixed> $data
     */
    public function saveComment(int $postId, ?int $commentId, array $data): CommentDto
    {
        $post = $this->postRepository->getById($postId);

        if ($post === null) {
            throw new RuntimeException('Příspěvek nebyl nalezen.');
        }

        if ($commentId !== null) {
            $comment = $this->commentRepository->getById($commentId);

            if ($comment === null || $this->toInt($comment->post_id) !== $postId) {
                throw new RuntimeException('Komentář nebyl nalezen.');
            }
        }

        $data['post_id'] = $postId;

        return $this->commentMapper->map($this->commentRepository->save($commentId, $data));
    }

    /**
     * Smaže komentář. Ověřuje, zda komentář patří k danému příspěvku.
     */
    public function deleteComment(int $postId, int $commentId): void
    {
        $comment = $this->commentRepository->getById($commentId);

        if ($comment === null || $this->toInt($comment->post_id) !== $postId) {
            throw new RuntimeException('Komentář nebyl nalezen.');
        }

        $this->commentRepository->delete($commentId);
    }

    /**
     * Smaže příspěvek včetně všech jeho komentářů v rámci jedné databázové transakce.
     * Tím je zajištěna integrita dat - buď se smaže vše, nebo nic.
     */
    public function deletePost(int $postId): void
    {
        $post = $this->postRepository->getById($postId);

        if ($post === null) {
            throw new RuntimeException('Příspěvek nebyl nalezen.');
        }

        $this->database->transaction(function () use ($postId): void {
            // Smazání všech navázaných komentářů
            foreach ($this->commentRepository->findByPostId($postId) as $comment) {
                $comment->delete();
            }

            // Smazání samotného příspěvku
            $this->postRepository->delete($postId);
        });
    }

    /**
     * Pomocná metoda pro bezpečný převod mixed hodnoty na int pro PHPStan.
     */
    private function toInt(mixed $value): int
    {
        if (!is_int($value) && !is_string($value)) {
            throw new RuntimeException('Databázová hodnota nemá očekávaný celočíselný typ.');
        }

        return (int) $value;
    }
}
