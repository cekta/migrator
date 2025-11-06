# Cekta/Migrator

tool to you migration

## Advantages

1. Migration can be located on any directory
2. Migration file name not required datetime, we use static method id() should return number (like unixtimestamp).
    Ordering via number
3. Migration is just common class with any dependencies

## Usage

1. install
    ```
    composer require cekta/migrator
    ```
   
2. Register commands in you cli
    ```php
    \Cekta\Migrator\Command\Migrate::class,
    \Cekta\Migrator\Command\Rollback::class,
    \Cekta\Migrator\Command\MakeMigration::class
    ```
   
3. [Create new migration](./tests/Example/MigrationMagic.php) via IDE or command make:migration

4. Register migration and dependency in you psr/container, see [full example](./tests/bin/cli.php)

### Migrate

```
php ./tests/bin/cli.php migrate -i
```

### Rollback

```
php ./tests/bin/cli.php migration:rollback
```

## Test for develop

```
make migrate
make rollback
```

### Requirements

1. docker
2. make

## Contact

* chat: https://t.me/dev_ru
* isssue: https://github.com/cekta/migrator/issues

