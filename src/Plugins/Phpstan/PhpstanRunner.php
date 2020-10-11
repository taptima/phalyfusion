<?php

namespace Phalyfusion\Plugins\Phpstan;

use Phalyfusion\Console\IOHandler;
use Phalyfusion\Model\ErrorModel;
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
            $path = "'{$path}'";
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
            foreach ($decoded['files'] as $filePath => $errors) {
                foreach ($errors['messages'] as $error) {
                    $prefix = getcwd() . '/';
                    if (substr($filePath, 0, strlen($prefix)) == $prefix) {
                        $filePath = substr($filePath, strlen($prefix));
                    }

                    $errorModel = new ErrorModel($error['line'], $error['message'], 'error', self::name);
                    $outputModel->appendError($filePath, $errorModel);
                }
            }
        }

        return $outputModel;
    }
}