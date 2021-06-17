<?php

namespace Phalyfusion\Plugins\Psalm;

use Phalyfusion\Console\IOHandler;
use Phalyfusion\Model\PluginOutputModel;
use Phalyfusion\Plugins\PluginRunner;

/**
 * Class PsalmRunner.
 */
class PsalmRunner extends PluginRunner
{
    protected const name = 'psalm';

    /**
     * PsalmRunner constructor.
     */
    public function __construct()
    {
        IOHandler::debug('Hello, Psalm!');
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareCommand(string $runCommand, array $paths): string
    {
        $runCommand = preg_replace('/\s--output-format=(\'.*?\'|".*?"|\S+)/', '', $runCommand);
        $runCommand = $this->addOption($runCommand, '--output-format=json');
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
            foreach ($decoded as $error) {
                $this->addError($outputModel, $error['file_path'], $error['message'], $error['line_from'], $error['severity']);
            }
        }

        return $outputModel;
    }
}
