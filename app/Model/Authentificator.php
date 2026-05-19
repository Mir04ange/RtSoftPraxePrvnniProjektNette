<?php

namespace App\Model;

use Nette;
use Nette\Security as Sec;

class Authenticator implements Sec\Authenticator
{
    public function __construct(private Nette\Database\Explorer $database) {}

    public function authenticate(string $user, string $password): Sec\Identity
    {
        // Tady si najdete uživatele v DB a porovnáte heslo
        $row = $this->database->table('users')->where('username', $user)->fetch();

        if (!$row || !Nette\Security\Passwords::verify($password, $row->password)) {
            throw new Sec\AuthenticationException('Špatné heslo.');
        }

        return new Sec\Identity($row->id, ['role' => 'admin']);
    }
}