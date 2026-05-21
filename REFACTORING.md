# Dokumentace refaktorizace Nette aplikace

Tento dokument vysvětluje provedené změny v architektuře aplikace, které směřují k lepší udržitelnosti a oddělení logiky pomocí **Repository patternu**.

## Hlavní principy změn

Původní aplikace obsahovala databázovou logiku přímo v presenterech, což ztěžovalo testování a opětovné použití kódu. Refaktorizace se zaměřila na následující body:

1.  **Vytvoření repozitářů**: Pro každou databázovou tabulku byl vytvořen samostatný repozitář v adresáři `app/Model`.
2.  **Přesun logiky**: Veškerá práce s `Nette\Database\Explorer` byla přesunuta z presenterů do metod repozitářů.
3.  **Dependency Injection**: Repozitáře jsou nyní předávány do presenterů pomocí konstruktoru, což je standardní a doporučený postup v Nette.
4.  **Konfigurace služeb**: Nové třídy byly zaregistrovány v `config/services.neon`.

## Přehled nových komponent

| Komponenta | Popis |
| :--- | :--- |
| **PostRepository** | Spravuje operace nad tabulkou `posts` (získání příspěvků, vkládání, aktualizace). |
| **CommentRepository** | Spravuje operace nad tabulkou `comments` (vkládání komentářů, filtrování podle příspěvku). |

## Jak se vytvářejí repozitáře

Při vytváření nového repozitáře postupujte následovně:

1.  **Vytvoření třídy**: V `app/Model` vytvořte třídu s názvem odpovídajícím tabulce (např. `User` -> `UserRepository`).
2.  **Injektování databáze**: Do konstruktoru si nechte předat `Nette\Database\Explorer`.
3.  **Definice metod**: Vytvořte metody pro specifické dotazy (např. `findActiveUsers()`), aby presenter nemusel znát strukturu databáze.
4.  **Registrace**: Přidejte repozitář do `config/services.neon` pod sekci `services`.

## Ukázka použití v Presenteru

Místo přímého volání `$this->database->table(...)` nyní používáme injektovanou službu:

```php
public function __construct(
    private PostRepository $postRepository,
) {
    parent::__construct();
}

public function renderDefault(): void
{
    $this->template->posts = $this->postRepository->findPublicPosts();
}
```

Tento přístup zajišťuje, že pokud se v budoucnu změní struktura databáze, stačí upravit kód na jednom místě – v příslušném repozitáři.
