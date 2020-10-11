<?php

namespace Phalyfusion\Plugins\Phpmd;

use Phalyfusion\Console\IOHandler;
use Phalyfusion\Model\ErrorModel;
use Phalyfusion\Model\PluginOutputModel;
use Phalyfusion\Plugins\PluginRunner;

/**
 * Class PhpmdRunner.
 */
class PhpmdRunner extends PluginRunner
{
    protected const name = 'phpmd';

    /**
     * PhpmdRunner constructor.
     */
    public function __construct()
    {
        IOHandler::debug('Hello, PHPMD!');
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareCommand(string $runCommand, array $paths): string
    {
        preg_match_all('/\'.*?\'|".*?"|\S+/', $runCommand, $matches);
        $matches[0][2] = 'json';

        if ($paths) {
            foreach ($paths as &$path) {
                $path = "'{$path}'";
            }
            $matches[0][1] = implode(',', $paths);
        }

        $runCommand = implode(' ', $matches[0]);

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
            foreach ($decoded['files'] as $file) {
                foreach ($file['violations'] as $error) {
                    $prefix   = getcwd() . '/';
                    $filePath = $file['file'];
                    if (substr($filePath, 0, strlen($prefix)) == $prefix) {
                        $filePath = substr($filePath, strlen($prefix));
                    }

                    $errorModel = new ErrorModel($error['beginLine'], $error['description'], 'error', self::name);
                    $outputModel->appendError($filePath, $errorModel);
                }
            }
        }

        return $outputModel;
    }
}