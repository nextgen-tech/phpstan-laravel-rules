# PHPStan Laravel Rules

This package provides collection of PHPStan rules for Laravel applications. For now it provides 2 new rules:

- disallow debug functions (`dd`, `ddd`, `dump`, `debug`, `print_r`, `var_dump` and `var_export`)
- disallow debug methods on Collections (`dd`, `dump` and `debug`)

## Installation

```sh
composer require nextgen-tech/phpstan-laravel-rules --dev
```

## Including extension to PHPStan

In `phpstan.neon` or `phpstan.neon.dist` file in `includes` section add:

```yaml
./vendor/nextgen-tech/phpstan-laravel-rules/extension.neon
```

After that file should looks like this:

```yaml
includes:
    - ./vendor/nunomaduro/larastan/extension.neon                # larastan extension, could be omited
    - ./vendor/nextgen-tech/phpstan-laravel-rules/extension.neon # extension from this package

parameters:
    paths:
        - app

    level: 8
```