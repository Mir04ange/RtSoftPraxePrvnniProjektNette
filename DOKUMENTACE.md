# Průvodce projektem a frameworkem Nette

Tento dokument slouží jako vysvětlení architektury tvého projektu a základních principů frameworku Nette, které jsme v aplikaci použili.

## 1. Co je to Nette?

Nette je český PHP framework pro tvorbu webových aplikací. Jeho hlavními pilíři jsou:
- **Produktivita**: Automatizuje běžné úkoly (routování, formuláře, šablony).
- **Bezpečnost**: Automaticky chrání před XSS, CSRF a dalšími útoky.
- **DI Container**: Moderní způsob správy závislostí mezi třídami.

## 2. Architektura projektu (MVP)

Projekt využívá vzor **Model-View-Presenter**, který jsme rozšířili o další vrstvy pro lepší čistotu kódu.

### Presentery (V-P)
Nacházejí se v `app/Module/Front/Presenters`. Jsou to "dirigenti" aplikace.
- Přijímají požadavky od uživatele (např. kliknutí na odkaz).
- Komunikují s **Fasádou**, aby získali nebo uložili data.
- Předávají data do **Šablon** (`.latte`).
- *Příklad:* `PostPresenter` zobrazuje detail článku a zpracovává přidání komentáře.

### Model (M)
Zde leží veškerá logika práce s daty. Rozdělili jsme ji na:
1. **Repozitáře** (`app/Model/*Repository.php`): Přímá komunikace s databází přes Nette Database Explorer.
2. **Fasády** (`app/Model/Facade`): Vyšší vrstva, která může pracovat s více repozitáři najednou a obaluje operace do transakcí.
3. **DTO (Data Transfer Objects)** (`app/Model/DTO`): Jednoduché přepravky na data. Místo abychom v celém kódu pracovali s databázovými řádky, používáme tyhle čisté objekty.
4. **Mappery** (`app/Model/Mapper`): Třídy, které umí "přeložit" řádek z databáze na DTO objekt.

## 3. Klíčové principy v tvém kódu

### CRUD a BaseRepository
Vytvořili jsme `BaseRepository`, od kterého dědí ostatní. Obsahuje metodu `save()`, která je "chytrá" – pokud jí předáš ID, provede UPDATE, pokud ne, provede INSERT. Tím se vyhneš duplicitnímu kódu.

### Fasády a Transakce
Při mazání příspěvku (`PostFacade::deletePost`) používáme **databázovou transakci**. To zajišťuje, že se buď smaže příspěvek i všechny jeho komentáře úspěšně, nebo se v případě chyby neprovede nic. Databáze tak zůstane v konzistentním stavu.

### DTO a Typová bezpečnost
Díky DTO objektům a PHPStanu máme kód pod kontrolou. Pokud se pokusíš v šabloně vypsat vlastnost, která neexistuje, nebo v kódu použiješ špatný datový typ, PHPStan tě na to upozorní dřív, než aplikaci spustíš.

### Zabezpečení
V `EditPresenter` a `PostPresenter` jsme přidali kontroly:
```php
if (!$this->getUser()->isLoggedIn()) {
    $this->redirect('Sign:in');
}
```
Tím jsme zajistili, že anonymní návštěvník může články jen číst, ale nemůže je měnit ani mazat.

## 4. Jak pracovat s projektem

- **Nová tabulka?** Vytvoř Repozitář dědící z `BaseRepository`, DTO, Mapper a zaregistruj je do `services.neon`.
- **Nová stránka?** Vytvoř Presenter a k němu příslušnou `.latte` šablonu.
- **Kontrola kódu?** Spusť v terminálu `make ps` (nebo přímo PHPStan), aby ses ujistil, že v kódu nejsou chyby.

---
*Tento projekt byl upraven tak, aby splňoval moderní standardy vývoje v Nette s důrazem na OOP a čistotu kódu.*
