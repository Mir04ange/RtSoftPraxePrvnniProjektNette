<?php

declare(strict_types=1);

namespace App\Module\Front\Presenters;

use App\Model\Facade\PostFacade;
use Nette;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use RuntimeException;

/**
 * Presenter pro vytváření, editaci a mazání příspěvků.
 * Přístup k těmto akcím je omezen pouze na přihlášené uživatele.
 */
final class EditPresenter extends Nette\Application\UI\Presenter
{
    public function __construct(
        private PostFacade $postFacade,
    ) {
        parent::__construct();
    }

    /**
     * Společná kontrola oprávnění pro všechny akce v tomto presenteru.
     */
    protected function startup(): void
    {
        parent::startup();
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in', ['backlink' => $this->storeRequest()]);
        }
    }

    /**
     * Akce pro vytvoření nového příspěvku.
     */
    public function actionCreate(): void
    {
        // Žádná speciální logika, pouze zobrazení formuláře
    }

    /**
     * Akce pro úpravu stávajícího příspěvku.
     */
    public function actionEdit(int $id): void
    {
        $post = $this->postFacade->getPost($id);

        if ($post === null) {
            $this->error('Příspěvek nebyl nalezen.');
        }

        $this->getComponent('postForm')
            ->setDefaults([
                'title' => $post->title,
                'content' => $post->content,
            ]);
    }

    /**
     * Akce pro smazání příspěvku.
     */
    public function actionDelete(int $id): void
    {
        try {
            $this->postFacade->deletePost($id);
            $this->flashMessage('Příspěvek byl smazán.', 'success');
        } catch (RuntimeException $exception) {
            $this->flashMessage('Při mazání příspěvku došlo k chybě: ' . $exception->getMessage(), 'error');
        }

        $this->redirect('Homepage:default');
    }

    /**
     * Komponenta formuláře pro příspěvek.
     */
    protected function createComponentPostForm(): Form
    {
        $form = new Form;
        $form->addText('title', 'Titulek:')
            ->setRequired('Prosím vyplňte titulek.');
        $form->addTextArea('content', 'Obsah:')
            ->setRequired('Prosím vyplňte obsah.');
        $form->addSubmit('send', 'Uložit a publikovat');
        $form->onSuccess[] = $this->postFormSucceeded(...);

        return $form;
    }

    /**
     * Zpracování úspěšně odeslaného formuláře příspěvku.
     */
    private function postFormSucceeded(Form $form, ArrayHash $data): void
    {
        $id = $this->toOptionalInt($this->getParameter('id'));
        
        try {
            $post = $this->postFacade->savePost($id, [
                'title' => $this->getStringValue($data, 'title'),
                'content' => $this->getStringValue($data, 'content'),
            ]);

            $this->flashMessage('Příspěvek byl úspěšně uložen.', 'success');
            $this->redirect('Post:show', $post->id);
        } catch (RuntimeException $exception) {
            $form->addError('Při ukládání došlo k chybě: ' . $exception->getMessage());
        }
    }

    /**
     * Pomocná metoda pro bezpečný převod na volitelné celé číslo.
     */
    private function toOptionalInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (!is_int($value) && !is_string($value)) {
            return null;
        }

        return (int) $value;
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
