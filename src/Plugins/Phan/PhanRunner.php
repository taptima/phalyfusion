<?php

namespace Phalyfusion\Plugins\Phan;

use Phalyfusion\Console\IOHandler;
use Phalyfusion\Model\ErrorModel;
use Phalyfusion\Model\PluginOutputModel;
use Phalyfusion\Plugins\PluginRunner;

/**
 * Class PhanRunner.
 */
class PhanRunner extends PluginRunner
{
    protected const name = 'phan';

    /**
     * PhanRunner constructor.
     */
    public function __construct()
    {
        IOHandler::debug('Hello, Phan!');
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareCommand(string $runCommand, array $paths): string
    {
        $runCommand = preg_replace('/(\s--output-mode(=|\s+?)|\s-m(=|\s*))(\'.*?\'|".*?"|\S+)/', '', $runCommand);
        $runCommand = $this->addOption($runCommand, '--output-mode=json');

        if ($paths) {
            foreach ($paths as &$path) {
                $relative = $this->getRelativePath(getcwd(), $path);
                $path     = "'{$relative}'";
            }
            $runCommand = preg_replace('/(\s--include-analysis-file-list(=|\s+?)|\s-m(=|\s*))(\'.*?\'|".*?"|\S+)/', '', $runCommand);
            $runCommand = preg_replace('/(\s-I(=|\s+?)|\s-m(=|\s*))(\'.*?\'|".*?"|\S+)/', '', $runCommand);
            $runCommand = $this->addOption($runCommand, '--include-analysis-file-list=' . implode(',', $paths));
        }

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
                $filePath   = $error['location']['path'];
                $errorModel = new ErrorModel($error['location']['lines']['begin'], $error['description'],
                    $error['type'], self::name);
                $outputModel->appendError($filePath, $errorModel);
            }
        }

        return $outputModel;
    }

    /**
     * Gets relative path. Needed because Phan is not able to use absolute path.
     *
     * @param $from
     * @param $to
     *
     * @return string
     */
    private function getRelativePath($from, $to): string
    {
        // some compatibility fixes for Windows paths
        $from = is_dir($from) ? rtrim($from, '\/') . '/' : $from;
        $to   = is_dir($to) ? rtrim($to, '\/') . '/' : $to;
        $from = str_replace('\\', '/', $from);
        $to   = str_replace('\\', '/', $to);

        // if "to" path is relative, return
        if ($to[0] != '/') {
            return $to;
        }

        $from    = explode('/', $from);
        $to      = explode('/', $to);
        $relPath = $to;

        foreach ($from as $depth => $dir) {
            // find first non-matching dir
            if ($dir === $to[$depth]) {
                // ignore this directory
                array_shift($relPath);
            } else {
                // get number of remaining dirs to $from
                $remaining = count($from) - $depth;
                if ($remaining > 1) {
                    // add traversals up to first matching dir
                    $padLength = (count($relPath) + $remaining - 1) * -1;
                    $relPath   = array_pad($relPath, $padLength, '..');

                    break;
                }// else {
                 //   $relPath[0] = './' . $relPath[0];
                 //}
            }
        }

        return implode('/', $relPath);
    }
}