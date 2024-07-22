# The task solution

Refactoring a code with the logics of calculating commissions for already made transactions.

[The task](TASK.md)

## Requirements

---

- PHP 8.3 or higher;

## Installation

---

```
> composer install
```
Copy ``.env.local.example`` to ``.env.local`` and fill in the variables 

## Usage

---

- EU countries can be changes in config parameters

```
> bin/console app:calculate-commissions input.txt
```

## PHPUnit

---

```
> vendor/bin/phpunit
```

## PHPStan

---

```
> vendor/bin/phpstan
```