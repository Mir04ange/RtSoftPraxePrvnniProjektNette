<?php

declare(strict_types=1);

namespace App\Module\Front\Presenters;

use App\Model\Facade\PostFacade;
use Nette;


final class HomepagePresenter extends Nette\Application\UI\Presenter
{
    public function __construct(
        private PostFacade $postFacade,
    ) {
        parent::__construct();
    }

    public function renderDefault(): void
    {
        $pagesPosts = 5;
        $this->template->posts = $this->postFacade->findPublicPosts($pagesPosts);
    }

    public function renderHelp(): void
    {
        // Použije šablonu Help/default.latte
    }
}
