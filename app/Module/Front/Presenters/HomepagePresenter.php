<?php

declare(strict_types=1);

namespace App\Module\Front\Presenters;

use App\Model\PostRepository;
use Nette;

final class HomepagePresenter extends Nette\Application\UI\Presenter
{
    public function __construct(
        private PostRepository $postRepository,
    ) {
        parent::__construct();
    }

    public function renderDefault(): void
    {
        $this->template->posts = $this->postRepository->findPublicPosts()->limit(5);
    }
}
