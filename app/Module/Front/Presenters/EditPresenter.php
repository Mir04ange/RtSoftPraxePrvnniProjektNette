<?php

declare(strict_types=1);

namespace App\Module\Front\Presenters;

use App\Model\Facade\PostFacade;
use Nette;
use Nette\Application\UI\Form;
use RuntimeException;

final class EditPresenter extends Nette\Application\UI\Presenter
{
    public function __construct(
        private PostFacade $postFacade,
    ) {
        parent::__construct();
    }

    protected function createComponentPostForm(): Form
    {
        $form = new Form;
        $form->addText('title', 'Titulek:')
            ->setRequired();
        $form->addTextArea('content', 'Obsah:')
            ->setRequired();
        $form->addSubmit('send', 'Uložit a publikovat');
        $form->onSuccess[] = $this->postFormSucceeded(...);

        return $form;
    }

    private function postFormSucceeded(Form $form, mixed $data): void
    {
        $id = $this->toOptionalInt($this->getParameter('id'));
        $post = $this->postFacade->savePost($id, [
            'title' => $this->getStringValue($data, 'title'),
            'content' => $this->getStringValue($data, 'content'),
        ]);

        $this->flashMessage('Příspěvek byl úspěšně uložen.', 'success');
        $this->redirect('Post:show', $post->id);
    }

    public function renderEdit(int $id): void
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

    public function actionDelete(int $id): void
    {
        try {
            $this->postFacade->deletePost($id);
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());
        }

        $this->flashMessage('Příspěvek byl smazán.', 'success');
        $this->redirect('Homepage:default');
    }

    private function toOptionalInt(mixed $value): ?int
    {
        if ($value === null) {
            return null;
        }

        if (!is_int($value) && !is_string($value)) {
            return null;
        }

        return (int) $value;
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
