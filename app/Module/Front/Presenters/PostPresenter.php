<?php

declare(strict_types=1);

namespace App\Module\Front\Presenters;

use App\Model\Facade\PostFacade;
use Nette;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use RuntimeException;

/**
 * Presenter pro zobrazení detailu příspěvku a správu jeho komentářů.
 */
final class PostPresenter extends Nette\Application\UI\Presenter
{
    public function __construct(
        private PostFacade $postFacade,
    ) {
        parent::__construct();
    }

    /**
     * Zobrazení detailu příspěvku a jeho komentářů.
     */
    public function renderShow(int $id): void
    {
        $post = $this->postFacade->getPost($id);

        if ($post === null) {
            $this->error('Příspěvek nebyl nalezen.');
        }

        $this->template->post = $post;
        $this->template->comments = $this->postFacade->findCommentsByPostId($id);
    }

    /**
     * Akce pro editaci komentáře. Přístupná pouze přihlášeným uživatelům.
     */
    public function actionEditComment(int $id, int $commentId): void
    {
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in', ['backlink' => $this->storeRequest()]);
        }

        $post = $this->postFacade->getPost($id);
        $comment = $this->postFacade->getComment($commentId);

        if ($post === null || $comment === null || $comment->postId !== $id) {
            $this->error('Komentář nebyl nalezen.');
        }

        $this->getComponent('commentForm')
            ->setDefaults([
                'name' => $comment->name,
                'email' => $comment->email,
                'content' => $comment->content,
            ]);
    }

    /**
     * Zobrazení šablony pro editaci komentáře.
     */
    public function renderEditComment(int $id, int $commentId): void
    {
        $post = $this->postFacade->getPost($id);
        $comment = $this->postFacade->getComment($commentId);

        if ($post === null || $comment === null) {
            $this->error('Data nebyla nalezena.');
        }

        $this->template->post = $post;
        $this->template->comment = $comment;
    }

    /**
     * Akce pro smazání komentáře. Přístupná pouze přihlášeným uživatelům.
     */
    public function actionDeleteComment(int $id, int $commentId): void
    {
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in', ['backlink' => $this->storeRequest()]);
        }

        try {
            $this->postFacade->deleteComment($id, $commentId);
            $this->flashMessage('Komentář byl smazán.', 'success');
        } catch (RuntimeException $exception) {
            $this->flashMessage('Při mazání komentáře došlo k chybě: ' . $exception->getMessage(), 'error');
        }

        $this->redirect('Post:show', $id);
    }

    /**
     * Komponenta formuláře pro komentář.
     */
    protected function createComponentCommentForm(): Form
    {
        $form = new Form;
        $form->addText('name', 'Jméno:')
            ->setRequired('Prosím vyplňte své jméno.');
        $form->addEmail('email', 'E-mail:');
        $form->addTextArea('content', 'Komentář:')
            ->setRequired('Prosím vyplňte obsah komentáře.');
        $form->addSubmit('send', 'Uložit komentář');
        $form->onSuccess[] = $this->commentFormSucceeded(...);

        return $form;
    }

    /**
     * Zpracování úspěšně odeslaného formuláře komentáře.
     */
    private function commentFormSucceeded(Form $form, ArrayHash $data): void
    {
        $postId = $this->toInt($this->getParameter('id'));
        $commentId = $this->toOptionalInt($this->getParameter('commentId'));

        try {
            $this->postFacade->saveComment($postId, $commentId, [
                'name' => $this->getStringValue($data, 'name'),
                'email' => $this->getStringValue($data, 'email'),
                'content' => $this->getStringValue($data, 'content'),
            ]);
            $this->flashMessage('Komentář byl úspěšně uložen.', 'success');
            $this->redirect('Post:show', $postId);
        } catch (RuntimeException $exception) {
            $form->addError('Při ukládání došlo k chybě: ' . $exception->getMessage());
        }
    }

    /**
     * Pomocná metoda pro bezpečný převod na int.
     */
    private function toInt(mixed $value): int
    {
        if (!is_int($value) && !is_string($value)) {
            throw new RuntimeException('Chybí povinný identifikátor.');
        }

        return (int) $value;
    }

    /**
     * Pomocná metoda pro bezpečný převod na volitelné int.
     */
    private function toOptionalInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return $this->toInt($value);
    }

    /**
     * Pomocná metoda pro bezpečné získání textové hodnoty z dat.
     */
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
