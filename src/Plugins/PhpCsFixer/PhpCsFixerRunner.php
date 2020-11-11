<?php

namespace Phalyfusion\Plugins\PhpCsFixer;

use Phalyfusion\Console\IOHandler;
use Phalyfusion\Model\PluginOutputModel;
use Phalyfusion\Plugins\PluginRunner;

/**
 * Class PhpCsFixerRunner.
 */
class PhpCsFixerRunner extends PluginRunner
{
    protected const name = 'php-cs-fixer';

    /**
     * PhanRunner constructor.
     */
    public function __construct()
    {
        IOHandler::debug('Hello, PHP-CS-Fixer!');
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareCommand(string $runCommand, array $paths): string
    {
        $runCommand = preg_replace('/\s--dry-run/', '', $runCommand);
        $runCommand = $this->addOption($runCommand, '--dry-run');
        $runCommand = $this->addOption($runCommand, '--format=gitlab');
        foreach ($paths as &$path) {
            $path = $this->preparePath($path);
        }
        $runCommand .= ' ' . implode(' ', $paths);

        return $runCommand;
    }

    /**
     * {@inheritdoc}
     */
    protected function parseOutput(string $output): PluginOutputModel
    {
        $outputModel = new PluginOutputModel();

        $ary         = explode("\n", $output);
        $encodedData = array_pop($ary);

        $decoded = json_decode($encodedData, true);
        if (!$decoded) {
            return $outputModel;
        }

        foreach ($decoded as $data) {
            $this->addError($outputModel, $data['location']['path'], $data['description']);
        }

        return $outputModel;
    }
}