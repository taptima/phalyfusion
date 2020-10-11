#!/usr/bin/php
<?php

$finder = PhpCsFixer\Finder::create()
    ->files()
    ->name('*.php')
    ->in(__DIR__ . '/src')
;

$config = PhpCsFixer\Config::create()
    ->setRules(
        Taptima\CS\RuleSetFactory::create([
            '@Taptima' => true,
        ])
        ->taptima()
        ->getRules()
    )
    ->registerCustomFixers(
        new Taptima\CS\Fixers()
    )
    ->setUsingCache(true)
    ->setFinder($finder)
;

if (version_compare(PHP_VERSION, '7.1', '>=')) {
  $config->setRules(array_merge($config->getRules(), [
    'list_syntax' => [
        'syntax' => 'short',
    ],
  ]));
}


return $config;
