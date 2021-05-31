<?php

namespace Phalyfusion;

use Composer\Autoload\ClassMapGenerator;
use Phalyfusion\Console\IOHandler;
use Phalyfusion\Model\PluginOutputModel;
use Phalyfusion\Plugins\PluginRunnerInterface;
use ReflectionClass;
use ReflectionException;

/**
 * Class Core.
 */
class Core
{
    /**
     * @var PluginRunnerInterface[]
     */
    private $plugins;

    /**
     * List of names of plugins to run.
     *
     * @var string[]
     */
    private $usedPlugins;

    /**
     * Run commands for each plugin.
     *
     * @var string[][]
     */
    private $runCommands;

    /**
     * Path to the root directory of the tool.
     *
     * @var string
     */
    private $rootDir;

    /**
     * Paths with source code to run analysis on.
     *
     * @var string[]
     */
    private $paths;

    /**
     * Core constructor.
     *
     * @param string   $rootDir     Path to the root directory of the tool
     * @param string[] $usedPlugins List of names of plugins to run
     * @param string[] $runCommands Run command for each plugin
     * @param string[] $paths       Paths with source code to run analysis on
     */
    public function __construct(string $rootDir, array $usedPlugins, array $runCommands, array $paths)
    {
        $this->plugins     = [];
        $this->rootDir     = $rootDir;
        $this->usedPlugins = $usedPlugins;
        $this->runCommands = $runCommands;
        $this->paths       = $paths;
        $this->loadPlugins();
    }

    /**
     * Run static code analysers.
     *
     * @return PluginOutputModel[]
     */
    public function runPlugins(): array
    {
        $output = [];
        foreach ($this->plugins as $plugin) {
            $pluginName = $plugin::getName();
            if (!array_key_exists($pluginName, $this->runCommands)) {
                IOHandler::error("{$pluginName} run failed!", "No run command for {$pluginName} provided in config");
                exit(1);
            }
            $output = array_merge($output, $plugin->run($this->runCommands[$pluginName], $this->paths));
        }

        return $output;
    }

    /**
     * Create instances of plugins classes stated in config.
     */
    private function loadPlugins(): void
    {
        $classMap = ClassMapGenerator::createMap($this->rootDir . '/src/Plugins');
        foreach ($classMap as $class => $path) {
            $interface = PluginRunnerInterface::class;

            try {
                $reflection = new ReflectionClass($class);
            } catch (ReflectionException $e) {
                IOHandler::error('Failed creating ReflectionClass', $e);
                exit(1);
            }

            if ($reflection->implementsInterface($interface)
                && $reflection->isInstantiable()
                && method_exists($class, 'getName') //suppress phpstorm inspection warning next line
                && in_array($class::getName(), $this->usedPlugins)) {
                $this->plugins[] = new $class();
                //remove this plugin from usedPlugins to check later if usedPlugins empty.
                unset($this->usedPlugins[array_search($class::getName(), $this->usedPlugins)]);
            }
        }
        if ($this->usedPlugins) {
            $unsupportedPluginNames = implode(', ', $this->usedPlugins);
            IOHandler::error('Phalyfusion run failed!',
                             "Unsupported plugins stated in config: {$unsupportedPluginNames}");
            exit(1);
        }
    }
}
