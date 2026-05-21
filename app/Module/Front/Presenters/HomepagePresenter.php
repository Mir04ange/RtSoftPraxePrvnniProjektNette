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

}