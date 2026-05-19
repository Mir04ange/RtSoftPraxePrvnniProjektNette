<?php
declare(strict_types=1);

namespace App\Module\Front\Presenters;

use Nette;
use Nette\Application\UI\Form;

final class PostPresenter extends Nette\Application\UI\Presenter
{
    public function __construct(
        private Nette\Database\Explorer $database,
    ) {
        parent::__construct();
    }

    public function renderShow(int $id): void
    {
        $post = $this->database->table('posts')->get($id);
        if (!$post) {
            $this->error('Příspěvek nebyl nalezen');
        }

        $this->template->post = $post;
        $this->template->comments = $post->related('comments')->order('created_at');
    }

    protected function createComponentCommentForm(): Form
    {
        $form = new Form;
        $form->addText('name', 'Jméno:')->setRequired();
        $form->addEmail('email', 'E-mail:');
        $form->addTextArea('content', 'Komentář:')->setRequired();
        $form->addSubmit('send', 'Publikovat komentář');

        $form->onSuccess[] = $this->commentFormSucceeded(...);

        return $form;
    }

    private function commentFormSucceeded(Form $form, \stdClass $data): void
    {
        $id = $this->getParameter('id');

        $this->database->table('comments')->insert([
            'post_id' => $id,
            'name' => $data->name,
            'email' => $data->email,
            'content' => $data->content,
        ]);

        $this->flashMessage('Děkuji za komentář', 'success');
        $this->redirect('this');
    }
}