# Rensen

The **Rensen** library provides a lightweight reactive system for PHP. It allows you to create reactive values that automatically update when dependencies change.

## Features

- Define **reactive values** (`R`) that automatically recompute when their dependencies change.
- Efficient dependency tracking with minimal state.

## Requirements

- PHP >= 8.0

## Installation

Using [Composer](https://getcomposer.org):

```shell
composer require xtompie/rensen
```

## Usage

The following example demonstrates how to define reactive values and react to their changes:

```php
use Xtompie\Rensen\R;

// Define reactive values
$a = new R(fn() => 1);
$b = new R(fn() => 2);

// Create a dependent reactive value
$c = new R(fn() => $a() + $b());

// React to changes in $c
new R(fn() => print("c: {$c()}\n"));

// Initial output:
// c: 3

// Change $a
$a(fn() => 10);

// Outputs:
// c: 12
```

### Explanation

1. **Reactive values** (`R`) are created using a callable.
2. Dependencies are automatically tracked. For example, `$c` depends on `$a` and `$b`.
3. When a dependency changes, all dependent values and reactions are updated.

## Why Rensen?

Rensen brings a reactive programming model to PHP. Inspired by modern frontend frameworks, it enables automatic propagation of changes in a simple and efficient way.
