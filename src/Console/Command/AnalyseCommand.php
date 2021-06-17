<?php

namespace Phalyfusion\Console\Command;

use Nette\Neon\Exception as NeonException;
use Nette\Neon\Neon;
use Phalyfusion\Console\IOHandler;
use Phalyfusion\Console\OutputGenerator;
use Phalyfusion\Core;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AnalyseCommand
 * Default and main command of the tool.
 */
class AnalyseCommand extends Command
{
    /**
     * Path to the root directory of the tool.
     *
     * @var string
     */
    private $rootDir;

    /**
     * AnalyseCommand constructor.
     *
     * @param string $rootDir
     */
    public function __construct(string $rootDir)
    {
        $this->rootDir = $rootDir;
        parent::__construct();
    }

    /**
     * Called after constructor.
     */
    protected function configure()
    {
        $this
            ->setName('analyse')
            ->setDescription('Initiate analysis.')
            ->setHelp('This command will execute analysers stated in config file')
            ->addOption(
                'config',
                'c',
                InputOption::VALUE_REQUIRED,
                'Path to neon config file. phalyfusion.neon located in project root is used by default.',
                'phalyfusion.neon'
            )
            ->addOption(
                'format',
                'f',
                InputOption::VALUE_REQUIRED,
                'Output format. Avaliable formats: table, json, checkstyle',
                'table'
            )
            ->addOption(
                'no-progress',
                'p',
                InputOption::VALUE_NONE,
                'Disables progress bar'
            )
            ->addArgument(
                'files',
                InputArgument::IS_ARRAY | InputArgument::OPTIONAL,
                'Paths to files with source code to run analysis on. Separate multiple with a space. Do not pass directories. File paths from command lines stated in phalyfusion.neon runCommands will be used by default'
            )
        ;
    }

    /**
     * Called on tool run.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        IOHandler::initialize($input, $output);

        IOHandler::debug('CWD: ' . getcwd());
        IOHandler::debug('ROOT: ' . $this->rootDir);

        $paths       = IOHandler::$input->getArgument('files');
        $config      = $this->readConfig();
        $usedPlugins = $config['plugins']['usePlugins'];
        $runCommands = $config['plugins']['runCommands'];

        foreach ($runCommands as $key => $value) {
            if (gettype($runCommands[$key]) != 'array') {
                $runCommands[$key] = [$value];
            }
        }

        if (!$usedPlugins) {
            IOHandler::error('One or more plugins should be used', 'No plugins to use are stated in config');
            exit(1);
        }

        $core = new Core($this->rootDir, $usedPlugins, $runCommands, $paths);
        switch (IOHandler::$input->getOption('format')) {
            case 'table':
                OutputGenerator::tableOutput($core->runPlugins());

                break;
            case 'json':
                OutputGenerator::jsonOutput($core->runPlugins());

                break;
            case 'checkstyle':
                OutputGenerator::checkstyleOutput($core->runPlugins());

                break;
            default:
                $format = IOHandler::$input->getOption('format');
                IOHandler::error("Output format {$format} not available. Use 'help analyse' to show available formats");
                exit(1);
        }

        return 0;
    }

    /**
     * Parse config file.
     *
     * @return array decoded Neon config
     */
    private function readConfig(): array
    {
        $configFile = IOHandler::$input->getOption('config');
        if (!file_exists($configFile)) {
            IOHandler::error("Config not found at {$configFile}");
            exit(1);
        }
        $neon = file_get_contents($configFile);

        try {
            $decoded = Neon::decode($neon);
        } catch (NeonException $e) {
            IOHandler::error("Failed parsing config ({$configFile})", $e);
            exit(1);
        }

        IOHandler::debug("CONFIG: {$configFile}");

        return $decoded;
    }
}