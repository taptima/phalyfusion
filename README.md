# Phalyfusion [![Tweet](https://img.shields.io/twitter/url/http/shields.io.svg?style=social)](https://twitter.com/intent/tweet?text=Combine%20the%20output%20of%20php%20static%20analyzers%20into%20a%20single%20convenient%20format%20with%20Phalyfusion%21&url=https://github.com/taptima/phalyfusion&hashtags=php,phpstan,phan,psalm,staticanalyzer,developers)
[![Latest Stable Version](https://poser.pugx.org/taptima/phalyfusion/v)](//packagist.org/packages/taptima/phalyfusion)
[![Latest Unstable Version](https://poser.pugx.org/taptima/phalyfusion/v/unstable)](//packagist.org/packages/taptima/phalyfusion)
[![License](https://poser.pugx.org/taptima/phalyfusion/license)](//packagist.org/packages/taptima/phalyfusion)

**Phalyfusion** is a tool for convenient and effective usage of multiple PHP static code analysers and QA tools.
It runs analysers, combines its outputs and makes a single nice output in various formats:
  - Nice PHPStan-like table console output, groups errors by the file.
  - Checkstyle
  - Json


Currently supported analysers and tools:
  - [PHPStan](https://phpstan.org/)
  - [Phan](https://github.com/phan/phan)
  - [Psalm](https://psalm.dev/)
  - [PHPMD](https://phpmd.org/)
  - [PHP-CS-Fixer](https://github.com/FriendsOfPHP/PHP-CS-Fixer). Run PHP-CS-Fixer with `--dry-run` option.

## Table of contents
- [How it works](#how-it-works)
- [Installation](#installation)
- [Usage](#usage)
- [Contributing](#contributing)

## How it works
For example, phpstan, phan and phpmd are required in the project.
Then the analyzer output looks like this (for example phan):

![Phan output](/docs/images/phan_output.png)

Phalyfusion combines the output of all analyzers and qa tools, groups them by file, and sorts them by line numbers.
The resulting output looks like this:

![Phalyfusion output](/docs/images/phalyfusion_out_1.png)

## Installation
```shell script
composer require --dev taptima/phalyfusion dev-master
```
Composer will install Phalyfusion’s executable in its ```bin-dir``` which defaults to ```vendor/bin```.

**Analysers should be installed individually.**

## Usage
After installing Phalyfusion you need to create `phalyfusion.neon` configuration file in the project root.

### Config sample
```neon
plugins:
    usePlugins:
        - phan
        - phpstan
        - psalm
        - phpmd
        - php-cs-fixer

    runCommands:
        phan:           bin/phan -k .phan/config.php
        phpstan:        bin/phpstan analyse -c phpstan.neon --level 7
        psalm:          bin/psalm -c psalm.xml
        phpmd:          bin/phpmd src text cleancode
        php-cs-fixer:   "bin/php-cs-fixer fix --config=.php_cs"
```
Provide names of analysers (plugins) you want to use in `usePlugins`. Choose from: `phan`, `phpstan`, `psalm`, `phpmd` or `php-cs-fixer`.
Provide command lines to run stated analysers. Paths are resolved relative to current working directory (the directory where from you are running Phalyfusion)

- Note that each analyser should be individually installed and configured.
- All supported by individual analysers arguments and options can be used in the corresponding command line (runCommands)
- Output formats of the analysers are overridden. To choose Phalyfusion output format use --format option when running.
- File\path arguments of analysers are NOT guaranteed to be overridden in case you pass such argument to Phalyfusion.
- Do not state path/files options/arguments in runCommands, use paths argument of Phalyfusion or configure it in configs.

### Usage
After configuring the tool and all used analysers run Phalyfusion. 
```bash
$ php phalyfusion analyse [options] [--] [<files>...]
```
The `analyse` is a default command to run all connected plugins, so it is optional to specify it. The simplest run command looks like:
```bash
$ php phalyfusion
$ php phalyfusion analyse
```

Type `$ php phalyfusion analyse --help` to show all available options and arguments.

#### Arguments
The `files` argument is Paths to files with source code to run analysis on. Separate multiple with a space. Do not pass directories. File paths from command lines stated in phalyfusion.neon runCommands will be used by default.

#### Options
The `-c`, `--config` option is a path to neon config file. `phalyfusion.neon` located in project root is used by default.

The `-f`, `--format` option for the output format. Supported formats are `table` (default one), `json`, `checkstyle`.

The `-p`, `--no-progress` option to disable progress bar.

## Contributing
See [CONTRIBUTING](CONTRIBUTING.md) file.
