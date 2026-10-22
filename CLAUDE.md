# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Overview

`aw-studio/laravel-bitflags` is a small Laravel package (PHP ^8.2, Laravel 11 / 12 / 13) for storing multiple flags in a single integer column as a bitmask. Flags must be powers of two. See `README.md` for the user-facing API.

## Commands

```bash
composer install
composer test                                   # vendor/bin/phpunit
vendor/bin/phpunit --filter test_method_name    # single test
vendor/bin/phpunit tests/ScopeBitflagsTest.php  # single file
```

Tests run in random order (`executionOrder="random"` in `phpunit.xml.dist`) and fail on warnings/risky tests.

Code style is defined in `.php-cs-fixer.dist.php` (php-cs-fixer is not a dev dependency; run it via a global install, e.g. `php-cs-fixer fix`).

## Architecture

The package has three cooperating pieces, which together form the public API:

- **`src/Casts/Bitflags.php`** — Eloquent cast. `get()` turns the stored int into an **array of individual flags** (e.g. `5` → `[1, 4]`); `set()` accepts either an array of flags (OR'ed together, validated as powers of two) or a raw int. So on a model, the cast attribute is an array, not an int.
- **`src/helpers.php`** — global functions autoloaded via composer `files`: `addBitflag`, `removeBitflag`, `inBitmask`, `getBitmask`, `isPowerOfTwo`. They all accept `array|int` for the bitmask so they work directly with the cast's array output (`getBitmask()` normalizes arrays to an int). Each is wrapped in `function_exists`.
- **`src/BitflagsServiceProvider.php`** — auto-discovered provider that registers macros on `Illuminate\Database\Query\Builder`: `whereBitflag`, `whereBitflags` (all flags set), `whereBitflagIn` (any flag set), `whereBitflagNot` (flag unset or column null). Some use `whereRaw` with interpolated column/flag values.

Power-of-two validation is duplicated between `getBitmask()` in helpers and `Bitflags::isPowerOfTwo()` in the cast.

## Tests

Tests use Orchestra Testbench (`tests/TestCase.php`) against in-memory SQLite. `TestCase` registers the service provider and runs `tests/TestSupport/create_test_models_table.php` on setup. `tests/TestSupport/TestModel` (flags `FOO=1`, `BAR=2`, `BAZ=4`, `status` cast with `Bitflags`) and its factory are the fixtures for all tests.
