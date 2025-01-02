# Rensen

The **Rensen** library provides a lightweight and flexible reactive system for PHP. It allows you to define reactive signals and effects that automatically update when dependencies change. This makes it ideal for scenarios where changes in one part of the system should propagate to other parts automatically.

## Features

- Define **signals** (`R` instances) with computed values.
- Automatically track dependencies between signals and recompute values as needed.
- React to changes in signals by defining **effects**.
- Lightweight and efficient, with minimal global state.

## Requirements

- PHP >= 8.1

## Installation

Using [composer](https://getcomposer.org):

```shell
composer require xtompie/rensen
```

## Usage

The following example demonstrates how to define signals, set dependencies between them, and react to their changes:

```php
use Xtompie\Rensen\R;

// Define independent signals
$A1 = new R(fn() => 1);
$A2 = new R(fn() => 2);

// Define a dependent signal
$A3 = new R(fn() => $A1() + $A2());

// Define an effect to react to changes in `A3`
new R(fn() => print("A3: {$A3()}\n"));

// At this point, the effect executes and prints the initial value of A3:
// Output: A3: 3

// Change the value of A1
$A1(fn() => 10);

// The effect executes again and prints the updated value of A3:
// Output: A3: 12

```

Explanation

1. **Signals** (`R`) are defined with a `callable` that computes their value.
2. Dependencies between signals are automatically tracked. For example, `a3` depends on `a1` and `a2`.
3. When any dependency changes, all dependent signals and effects are recomputed.

## Why Rensen?

Rensen is inspired by the principles of reactivity found in modern frontend frameworks, bringing the same simplicity and power to PHP. Its lightweight and declarative approach makes it a great choice for building reactive systems in your PHP applications.
