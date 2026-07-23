# PHP Env Validator

A lightweight, zero-dependency CLI tool for PHP developers to ensure local `.env` files are in sync with `.env.example`.

If your team frequently updates `.env.example` with new keys, this tool will help you find and fill in those missing keys locally without having to manually compare files.

## Features

- **Zero Dependencies**: Pure PHP, no bloated vendor folders.
- **Bulk or Interactive Prompts**: If keys are missing, the tool gives you the option to instantly append all missing keys as empty variables, or to be prompted one-by-one to provide values.
- **Smart Generation**: If you are missing an `.env.example` file, the tool can automatically generate a secure one for you by stripping the secrets from your existing `.env` file. If both are missing, it can initialize them for you.
- **CI/CD Friendly**: Run validation checks during your deployment or as a Git pre-commit hook.

## Installation

Install globally or locally in your project via Composer:

```bash
composer require e-weaver/php-env-validator --dev
```

## Usage

Simply run the executable from the root of your project (where your `.env` and `.env.example` reside):

```bash
./vendor/bin/env-validate
```

The tool will parse your `.env.example`, compare it against your `.env`, and prompt you to fill in any missing keys.

## License

MIT License
