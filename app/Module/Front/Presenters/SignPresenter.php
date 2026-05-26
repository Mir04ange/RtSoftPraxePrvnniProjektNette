<?php

declare(strict_types=1);

namespace App\Module\Front\Presenters;

use Nette;
use Nette\Application\UI\Form;

/**
 * Presenter pro správu autentizace (přihlašování a odhlašování).
 */
final class SignPresenter extends Nette\Application\UI\Presenter
{
    /**
     * Pokud je uživatel již přihlášen a snaží se jít na přihlašovací stránku,
     * přesměrujeme ho na úvodní stránku.
     */
    public function actionIn(): void
    {
        if ($this->getUser()->isLoggedIn()) {
            $this->redirect('Homepage:default');
        }
    }

    /**
     * Akce pro odhlášení uživatele. Provádí se přímo bez nutnosti potvrzovat formulář.
     */
    public function actionOut(): void
    {
        $this->getUser()->logout();
        $this->flashMessage('Byli jste odhlášeni.', 'info');
        $this->redirect('Homepage:default');
    }

    /**
     * Komponenta přihlašovacího formuláře.
     */
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

    /**
     * Zpracování úspěšně odeslaného přihlašovacího formuláře.
     */
    private function signInFormSucceeded(Form $form, mixed $data): void
    {
        try {
            $this->getUser()->login(
                $this->getStringValue($data, 'username'),
                $this->getStringValue($data, 'password'),
            );
            $this->flashMessage('Byli jste úspěšně přihlášeni.', 'success');
            
            $backlink = $this->getParameter('backlink');
            if (is_string($backlink)) {
                $this->restoreRequest($backlink);
            }
            
            $this->redirect('Homepage:default');
        } catch (Nette\Security\AuthenticationException) {
            $form->addError('Nesprávné přihlašovací jméno nebo heslo.');
        }
    }

    /**
     * Pomocná metoda pro bezpečné získání textové hodnoty z formulářových dat.
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
