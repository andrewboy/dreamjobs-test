# DREAMJOBS-TEST

## Task description

[task description](docs/dj-be-probafeladat.docx)

## Technical requirements

- php8.1
- mysql
- webserver (apache, nginx etc.)
- composer

## Install

1. make .env file and fill with valid data

    ```shell
    cp .env.example .env
    ```

1. install packages via composer

    ```shell
    composer install
    ```

1. generate key

    ```shell
    php artisan key:generate
    ```

1. link storage

    ```shell
    php artisan storage:link
    ```

1. create database tables

    ```shell
    php artisan migrate
    ```

1. generate seed data

    ```shell
    php artisan db:seed
    ```

1. generate API documentation

    ```shell
    php artisan lrd:generate
    ```

## Description

Tegnap sikerült időt allokálni a feladatra, így el tudtam készíteni a mai napra. Hát kezdjük is az elejénél.

## Server setup

Én dockerrel oldottam meg a virtuális szervert, de mivel laravel "out of the box" ad lehetőséget futtatására és hallottam, hogy ti vagrantot használtok, azzal is könnyen életre kelthető és nem küldök hozzá külön docker config-ot. Itt annyit kiemelnék, hogy javasolt a technikai megkötéseket figyelembe venni, különben előfordulhat, hogy nem fog működni.

## Migration

Itt artisan-al generáltam egy skeleton migrációs fájlt, melyet a *database/migrations* könyvtárban találtok és az általatok megadott paraméterek szerint kitöltve generáltam le egy "items" táblát.

## Factories

Virtuális adatok létrehozásához "factory"-t használtam, melyet a *database/factories* könyvtárban találtok meg. Teszt adatok létrehozásáoz fakert hívtam segítségül.

## Seeding

Táblák feltöltését adatokkal seeding-el oldottam meg, melyet *database/seeds* könyvtárban találtok. Itt alapértelmezetten 55-et állítottam be, ami tetszés szerint módosítható. Ezt bekötöttem az általános DatabaseSeeder osztályba, mellyel *artisan db:seed* paranccsal egy általános feltöltés indítható.

### Routing

Mivel API interfészt kellett implementálni, ezért *routes/api.php-ban* route-ok létrehozásához *Route::apiResource*-t használtam, ami biztosítja a szükséges index, show, store, update, destroy route-okat. Itt nem kell külön hozzáadni az api middleware-t, mert már tartalmazza.

## Validation

Validálást "FormRequest" osztállyal oldottam meg, így jól elkülöníthetőek és helyén kezelhetőek az egyes request validációk.

## Model

Létrehoztam egy Item modelt, ahol engedélyeztem a kitölthető mezőket, illetve jeleztem, hogy az adott model rendelkezik factory-val. Itt a creating, updating, saving eseményekre kötöttem a name és description mezők "tisztítását", melyet egy külső package segítségével oldottam meg.

## Controller

Az *app/Http/Controllers* alá *Api* könyvtárban különítettem el ezt a controller-t, jelezve funkcióját. Mivel API és JSON formátum a visszatérés típusa, ezért az összes metódus visszatérési típusa JsonResponse hintelésű, a beépített JsonResource osztály biztosítja az adat megfelelő struktúráját. A request-ek dependency injection-el lettek becsatolva, csakúgy mint az adott model, ami a route model binding alapján a rendszer generálja. Lehetett volna az adat előállítását külön kiszervezni egy service-be és azt is DI-al becsatolni, ahogy a egyedi JsonResource-ot létrehozni item-eknek, de a feladat összetettsége miatt ez nem volt szükséges. Lista lekérdezésekor a lapozást a laravel beépített megoldásával készítettem el, szűrés csak akkor történik, ha az adott paraméter jelen van.

## Tests

Mivel egyedi osztályokat nem nagyon hoztam létre, ezért nem készítettem Unit teszteket. A controllert sem szokták Unit test-elni. Az összes létrehozott route visztont Feature tesztelve van, a hibás működés is le van tesztelve. A teszt névtér és könyvtárstruktúra követi a tesztelendő kód könyvtárstruktúráját és névterét, így könnyen visszakereshető a tesztből a tesztelendő kód. Bizonyos teszteknél, például hibás működés tesztelésekor dataprovider-t használtam, ahol listába rendezhetőek a tesztelendő esetek. Stub, osztály mockolás stb. a feladat összetettsége miatt szükségtelen volt.
A tesztek a következő paranccsal futtathatóak le:

```shell
php artisan test
```

## Documentation

Dokumentáció készítéséhez egy külön composer package-et használtam: "rakutentech/laravel-request-docs". Ennek a package-nek nagy előnye, hogy nem kell a dokumentálandó részeknél comment-be írni a dokumentációt, hanem a kódból készít dokumentációt. Emellett egy webes API interfész-t is biztosít, ahol az adott API endpointok könnyen lekérdezhetőek. Azt nem állítom, hogy minden project-hez ezt a csomagot használnám, de ide tökéletes. Fontos, hogy a .env könyvtárban az "APP_URL" jól legyen kitöltve, mert ez alapján állítja elő az URL-eket! Generlás után a dokumentáció elérési útja a */request-docs* route-on érhető el.

## PSR12

Az általam írt kódok PSR12-re lettek alakítva, amit phpcs-vel ellenőriztem.

## kódminőség javítása github hook-okkal

Github hookokról hallottam már, de még nem írtam. Én a github "nagytestvérével" a gitlab-al dolgoztam és ott írtam CI/CD folyamatokat. De ha meg kéne oldanom gitlabon, akkor a CI körbe írnék egy task-ot, amelybe egy phpcs-fixert raknék.
