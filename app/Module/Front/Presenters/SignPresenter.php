<?php

declare(strict_types=1);

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

    private function signInFormSucceeded(Form $form, mixed $data): void
    {
        try {
            $this->getUser()->login(
                $this->getStringValue($data, 'username'),
                $this->getStringValue($data, 'password'),
            );
            $this->redirect('Homepage:default');
        } catch (Nette\Security\AuthenticationException) {
            $form->addError('Nesprávné přihlašovací jméno nebo heslo.');
        }
    }

    protected function createComponentSignOutForm(): Form
    {
        $form = new Form;
        $form->addSubmit('send', 'Odhlásit');
        $form->onSuccess[] = $this->signOutFormSucceeded(...);

        return $form;
    }

    public function signOutFormSucceeded(Form $form, mixed $data): void
    {
        $this->getUser()->logout();
        $this->flashMessage('Byli jste odhlášeni.');
        $this->redirect('Homepage:default');
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
