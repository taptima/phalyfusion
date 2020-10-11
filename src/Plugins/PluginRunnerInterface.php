<?php

namespace Phalyfusion\Plugins;

use Phalyfusion\Model\PluginOutputModel;

/**
 * Interface PluginRunnerInterface.
 */
interface PluginRunnerInterface
{
    /**
     * @return string
     */
    public static function getName(): string;

    /**
     * @param string   $runCommand
     * @param string[] $paths      Paths with source code to run analysis on
     *
     * @return PluginOutputModel
     */
    public function run(string $runCommand, array $paths): PluginOutputModel;
}