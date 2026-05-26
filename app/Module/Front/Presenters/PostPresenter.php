<?php

declare(strict_types=1);

namespace App\Module\Front\Presenters;

use App\Model\Facade\PostFacade;
use Nette;
use Nette\Application\UI\Form;
use RuntimeException;

final class PostPresenter extends Nette\Application\UI\Presenter
{
    public function __construct(
        private PostFacade $postFacade,
    ) {
        parent::__construct();
    }

    public function renderShow(int $id): void
    {
        $post = $this->postFacade->getPost($id);

        if ($post === null) {
            $this->error('Příspěvek nebyl nalezen.');
        }

        $this->template->post = $post;
        $this->template->comments = $this->postFacade->findCommentsByPostId($id);
    }

    public function renderEditComment(int $id, int $commentId): void
    {
        $post = $this->postFacade->getPost($id);
        $comment = $this->postFacade->getComment($commentId);

        if ($post === null || $comment === null || $comment->postId !== $id) {
            $this->error('Komentář nebyl nalezen.');
        }

        $this->template->post = $post;
        $this->template->comment = $comment;
        $this->getComponent('commentForm')
            ->setDefaults([
                'name' => $comment->name,
                'email' => $comment->email,
                'content' => $comment->content,
            ]);
    }

    protected function createComponentCommentForm(): Form
    {
        $form = new Form;
        $form->addText('name', 'Jméno:')
            ->setRequired();
        $form->addEmail('email', 'E-mail:');
        $form->addTextArea('content', 'Komentář:')
            ->setRequired();
        $form->addSubmit('send', 'Uložit komentář');
        $form->onSuccess[] = $this->commentFormSucceeded(...);

        return $form;
    }

    private function commentFormSucceeded(Form $form, mixed $data): void
    {
        $postId = $this->toInt($this->getParameter('id'));
        $commentId = $this->toOptionalInt($this->getParameter('commentId'));

        try {
            $this->postFacade->saveComment($postId, $commentId, [
                'name' => $this->getStringValue($data, 'name'),
                'email' => $this->getStringValue($data, 'email'),
                'content' => $this->getStringValue($data, 'content'),
            ]);
        } catch (RuntimeException $exception) {
            $form->addError($exception->getMessage());
            return;
        }

        $this->flashMessage('Komentář byl úspěšně uložen.', 'success');
        $this->redirect('Post:show', $postId);
    }

    public function actionDeleteComment(int $id, int $commentId): void
    {
        try {
            $this->postFacade->deleteComment($id, $commentId);
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());
        }

        $this->flashMessage('Komentář byl smazán.', 'success');
        $this->redirect('Post:show', $id);
    }

    private function toInt(mixed $value): int
    {
        if (!is_int($value) && !is_string($value)) {
            throw new RuntimeException('Chybí povinný identifikátor.');
        }

        return (int) $value;
    }

    private function toOptionalInt(mixed $value): ?int
    {
        if ($value === null) {
            return null;
        }

        return $this->toInt($value);
    }

    private function getStringValue(mixed $data, string $key): string
    {
        if (!is_object($data)) {
            return '';
        }

        $values = (array) $data;
        $value = $values[$key] ?? '';

        return is_scalar($value) ? (string) $value : '';
    }
}
