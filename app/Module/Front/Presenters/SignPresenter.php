<?php
namespace App\Module\Front\Presenters;

use Nette;
use Nette\Application\UI\Form;

final class SignPresenter extends Nette\Application\UI\Presenter
{
    protected function createComponentSignInForm(): Form
    {
        $form = new Form;
        $form->addText('username', 'Uživatelské jméno:')
            ->setRequired('Prosím vyplňte své uživatelské jméno.');

        $form->addPassword('password', 'Heslo:')
            ->setRequired('Prosím vyplňte své heslo.');

        $form->addSubmit('send', 'Přihlásit');

        $form->onSuccess[] = $this->signInFormSucceeded(...);
        return $form;
    }

    private function signInFormSucceeded(Form $form, \stdClass $data): void
    {
        try {
            $this->getUser()->login($data->username, $data->password);
            $this->redirect('Homepage:default');

        } catch (Nette\Security\AuthenticationException $e) {
            $form->addError('Nesprávné přihlašovací jméno nebo heslo.');
        }
    }

    protected function createComponentSignOutForm(): Form
    {
        $form = new Form;

        $form->addSubmit('send', 'Odhlásit');

        $form->onSuccess[] = [$this, 'signOutFormSucceeded'];

        return $form;
    }

    public function signOutFormSucceeded(Form $form, \stdClass $data): void
    {
        $this->getUser()->logout();

        $this->flashMessage('Byli jste odhlášeni.');

        $this->redirect('Homepage:default');
    }












}

