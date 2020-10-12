# Phalyfusion

Phalyfusion is a tool for convenient and effective usage of multiple PHP static code analysers.
It runs analysers, combines its outputs and makes a single nice output in various formats:
  - Nice PHPStan-like table console output, groups errors by the file.
  - Checkstyle
  - Json


Currently supported analysers:
  - [PHPStan](https://phpstan.org/)
  - [Phan](https://github.com/phan/phan)
  - [Psalm](https://psalm.dev/)
  - [PHPMD](https://phpmd.org/)

# Installation
```sh
composer require --dev taptima/phalyfusion
```
Composer will install Phalyfusion’s executable in its ```bin-dir``` which defaults to ```vendor/bin```.

**Analysers should be installed individually.**

# Usage
After installing Phalyfusion you need to create `phalyfusion.neon` configuration file in the project root.

### Config sample
```
plugins:
    usePlugins:
        - phan
        - phpstan
        - psalm
        - phpmd

    runCommands:
        phan:     bin/phan -k .phan/config.php
        phpstan:  bin/phpstan analyse -c phpstan.neon --level 7
        psalm:    bin/psalm -c psalm.xml
        phpmd:    bin/phpmd src text cleancode
```
Provide names of analysers (plugins) you want to use in `usePlugins`. Choose from: `phan` `phpstan` `psalm` `phpmd`.
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

The `-f`, `--format` option for the output format. Supported formats are `table` (default one), `json`, 'checkstyle'.

# Contributing
See [CONTRIBUTING](CONTRIBUTING.md) file.
