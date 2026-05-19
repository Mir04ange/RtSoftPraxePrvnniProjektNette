<?php
declare(strict_types=1);

namespace App\Module\Front\Presenters;

use Nette;
use Nette\Application\UI\Form;

final class HomepagePresenter extends Nette\Application\UI\Presenter
{
    public function __construct(
        private Nette\Database\Explorer $database,
    ) {}



    //render default
    public function renderDefault(): void
    {
        $this->template->posts = $this->database->table('posts')
            ->order('created_at DESC')
            ->limit(5);
    }
protected function createComponentCreatePost(): Form
    {
        $form = new Form;
        $form->addText('title', 'Titulek příspěvku:')->setRequired();
        $form->addTextArea('content', 'Obsah příspěvku:')->setRequired();
        $form->addSubmit('send', 'Uložit příspěvek');

        $form->onSuccess[] = $this->createPostSucceeded(...);

        return $form;
    }

    private function createPostSucceeded(Form $form, \stdClass $data): void
    {
        $post = $this->database->table('posts')->insert([
            'title' => $data->title,
            'content' => $data->content,
        ]);

        $this->flashMessage('Příspěvek byl úspěšně publikován.', 'success');
        $this->redirect('Post:show', $post->id); // Přesměruje rovnou na detail nového článku
    }
}