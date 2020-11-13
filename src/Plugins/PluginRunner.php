<?php

namespace Phalyfusion\Plugins;

use Exception;
use Phalyfusion\Console\IOHandler;
use Phalyfusion\Console\OutputGenerator;
use Phalyfusion\Model\ErrorModel;
use Phalyfusion\Model\PluginOutputModel;
use Symfony\Component\Process\Process;

/**
 * Class PluginRunner.
 * Base class of plugin runner classes.
 */
abstract class PluginRunner implements PluginRunnerInterface
{
    /**
     * Name of particular plugin.
     *
     * @return string
     */
    public static function getName(): string
    {
        return static::name;
    }

    /**
     * @param string[] $runCommands
     * @param string[] $paths
     *
     * @return PluginOutputModel[]
     */
    public function run(array $runCommands, array $paths): array
    {
        $name         = $this::getName();
        $pluginModels = [];

        foreach ($runCommands as $command) {
            OutputGenerator::nextAnalyzer($name);
            $runCommand = $this->prepareCommand($command, $paths);

            IOHandler::debug("---{$name}---");
            IOHandler::debug("{$runCommand}");

            $process = Process::fromShellCommandline($runCommand);

            try {
                $process->run(function ($type, $buffer) {
                    if ($type === Process::ERR) {
                        IOHandler::debug($buffer, false);
                    }
                });
            } catch (Exception $e) {
                IOHandler::error("{$name} run failed! Aborting.", $e);
                exit(1);
            }

            $output   = $process->getOutput();
            $exitcode = $process->getExitCode();
            if (!$output and $exitcode) {
                IOHandler::error("{$name} run failed! Empty output with exit code {$exitcode}", $process->getErrorOutput());
                exit(1);
            }

            $pluginModels[] = $this->parseOutput($output);
        }

        return $pluginModels;
    }

    /**
     * Prepares given command line for running the plugin.
     * Adds 'paths to analyse' argument/option if it is provided to Phalyfusion.
     * Removes/modifies arguments/options/flags of the commend line to make output nice and parseable
     * and to avoid unwanted behavior of the plugin.
     *
     * @param string   $runCommand
     * @param string[] $paths
     *
     * @return string
     */
    abstract protected function prepareCommand(string $runCommand, array $paths): string;

    /**
     * Parse $output of particular plugin into PluginOutputModel.
     *
     * @param string $output
     *
     * @return PluginOutputModel
     */
    abstract protected function parseOutput(string $output): PluginOutputModel;

    /**
     * Prepare path as command-line arugment, depending on OS.
     *
     * @param string $path
     *
     * @return string
     */
    protected function preparePath(string $path): string
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return "\"{$path}\"";
        }

        return "'{$path}'";
    }

    /**
     * Adds $option to $runCommand before other options and arguments.
     *
     * @param string $runCommand
     * @param string $option
     *
     * @return string
     */
    protected function addOption(string $runCommand, string $option): string
    {
        preg_match('/\'.*?\'|".*?"|\S+/', $runCommand, $matches);

        return substr_replace($runCommand, " {$option}", strlen($matches[0]), 0);
    }

    /**
     * Create and add error to plugin output model.
     *
     * @param PluginOutputModel $outputModel
     * @param string            $filePath
     * @param string            $message
     * @param int               $lineNumber
     * @param string            $type
     *
     * @return PluginOutputModel
     */
    protected function addError(PluginOutputModel $outputModel, string $filePath, string $message, int $lineNumber = 0, string $type = 'error'): PluginOutputModel
    {
        $errorModel = new ErrorModel($lineNumber, $message, $type, self::getName());
        $outputModel->appendError($this->prepareFilePath($filePath), $errorModel);

        return $outputModel;
    }

    /**
     * Prepare file path.
     *
     * @param string $filePath
     *
     * @return string
     */
    protected function prepareFilePath(string $filePath): string
    {
        $realPath = realpath($filePath);
        $prefix   = getcwd() . '/';
        if (substr($realPath, 0, strlen($prefix)) === $prefix) {
            return substr($realPath, strlen($prefix));
        }

        return $filePath;
    }
}