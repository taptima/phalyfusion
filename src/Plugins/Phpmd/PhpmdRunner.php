<?php

namespace Phalyfusion\Plugins\Phpmd;

use Phalyfusion\Console\IOHandler;
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
                $path = $this->preparePath($path);
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
                    $this->addError($outputModel, $file['file'], $error['description'], $error['beginLine']);
                }
            }
        }

        return $outputModel;
    }
}