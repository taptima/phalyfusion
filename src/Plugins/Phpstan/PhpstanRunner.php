<?php

namespace Phalyfusion\Plugins\Phpstan;

use Phalyfusion\Console\IOHandler;
use Phalyfusion\Model\PluginOutputModel;
use Phalyfusion\Plugins\PluginRunner;

/**
 * Class PhpstanRunner.
 */
class PhpstanRunner extends PluginRunner
{
    protected const name = 'phpstan';

    /**
     * PhpstanRunner constructor.
     */
    public function __construct()
    {
        IOHandler::debug('Hello, Phpstan!');
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareCommand(string $runCommand, array $paths): string
    {
        $runCommand = preg_replace('/\s--error-format(=|\s+?)(\'.*?\'|".*?"|\S+)/', '', $runCommand);
        $runCommand = $this->addOption($runCommand, '--error-format=json');
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

        $decoded = json_decode($output, true);
        if ($decoded) {
            foreach ($decoded['errors'] as $error) {
                $this->addError($outputModel, '', $error);
            }

            foreach ($decoded['files'] as $filePath => $errors) {
                foreach ($errors['messages'] as $error) {
                    $this->addError($outputModel, $filePath, $error['message'], $error['line']);
                }
            }
        }

        return $outputModel;
    }
}