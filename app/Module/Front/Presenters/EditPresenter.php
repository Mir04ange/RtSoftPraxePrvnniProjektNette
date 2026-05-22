<?php

declare(strict_types=1);

namespace App\Module\Front\Presenters;

use App\Model\PostRepository;
use Nette;
use Nette\Application\UI\Form;

final class EditPresenter extends Nette\Application\UI\Presenter
{
    public function __construct(
        private PostRepository $postRepository,
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

    private function postFormSucceeded(Form $form, \stdClass $data): void
    {
        $id = $this->getParameter('id');
        if ($id) {
            $post = $this->postRepository->update((int)$id, (array)$data);
        } else {
            $post = $this->postRepository->insert((array)$data);
        }

        $this->flashMessage('Příspěvek byl úspěšně publikován.', 'success');
        $this->redirect('Post:show', $post->id);
    }

    public function renderEdit(int $id): void
    {
        $post = $this->postRepository->getById($id);
        if (!$post) {
            $this->error('Post not found');
        }
        $this->getComponent('postForm')
            ->setDefaults($post->toArray());
    }
}
