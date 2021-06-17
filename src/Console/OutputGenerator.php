<?php

namespace Phalyfusion\Console;

use Phalyfusion\Model\ErrorModel;
use Phalyfusion\Model\PluginOutputModel;
use Symfony\Component\Console\Helper\ProgressBar;

/**
 * Class OutputGenerator.
 */
class OutputGenerator
{
    /**
     * @var ProgressBar
     */
    private static $progressBar;

    /**
     * @var bool
     */
    private static $isBarInit;

    public static function initProgressBar(int $stepsCnt): void
    {
        if (IOHandler::$input->getOption('format') == 'table') {
            IOHandler::$io->title(' ~~ Phalyfusion! ~~ ');
        }

        if (IOHandler::$input->getOption('no-progress')) {
            return;
        }

        IOHandler::$output->writeln('Analyzers in progress...');

        self::$progressBar = new ProgressBar(IOHandler::$output, $stepsCnt);
        self::$progressBar->setOverwrite(true);
        self::$progressBar->setFormat("<fg=white>[%current%/%max%] Current analyzer: %message%</>\n[%bar%]\n");

        self::$progressBar->setEmptyBarCharacter('<fg=white>|</>');
        self::$progressBar->setProgressCharacter('<fg=green>➤</>');
    }

    /**
     * @param string $analyzerName
     */
    public static function nextAnalyzer(string $analyzerName): void
    {
        if (self::$progressBar == null) {
            return;
        }

        self::$progressBar->setMessage($analyzerName);

        if (!self::$isBarInit) {
            self::$progressBar->start();
            self::$isBarInit = true;

            return;
        }

        self::$progressBar->advance();
    }

    public static function finishProgressBar(): void
    {
        if (self::$progressBar == null) {
            return;
        }

        self::$progressBar->setMessage('Done!');
        self::$progressBar->finish();
    }

    /**
     * @param PluginOutputModel[] $outputModels
     *
     * @return int
     */
    public static function tableOutput(array $outputModels): int
    {
        $model      = self::combineModels($outputModels);
        $errorCount = 0;

        if (!$model->getFiles()) {
            IOHandler::$io->success('No errors found!');

            return 0;
        }

        foreach ($model->getFiles() as $fileModel) {
            $rows = [];

            if ($fileModel->getPath() == '') {
                foreach ($fileModel->getErrors() as $errorModel) {
                    $rows[] = [$errorModel->getPluginName(), $errorModel->getMessage()];
                }

                IOHandler::$io->table(['Plugin', 'Not file specific errors'], $rows);
            } else {
                foreach ($fileModel->getErrors() as $errorModel) {
                    $rows[] = [$errorModel->getLine(), $errorModel->getPluginName(), $errorModel->getMessage()];
                    ++$errorCount;
                }
                IOHandler::$io->table(['Line', 'Plugin', $fileModel->getPath()], $rows);
            }
        }

        IOHandler::$io->error("{$errorCount} errors found!");

        return 1;
    }

    /**
     * @param PluginOutputModel[] $outputModels
     *
     * @return int
     */
    public static function jsonOutput(array $outputModels): int
    {
        $model = self::combineModels($outputModels);
        if (!$model->getFiles()) {
            IOHandler::$io->write('{}');

            return 0;
        }

        IOHandler::$io->write(json_encode($model));

        return 1;
    }

    /**
     * @param PluginOutputModel[] $outputModels
     *
     * @return int
     */
    public static function checkstyleOutput(array $outputModels): int
    {
        $model  = self::combineModels($outputModels);
        $output = '';

        foreach ($model->getFiles() as $fileModel) {
            $output .= '<file name="' . self::escape($fileModel->getPath()) . '">' . "\n";
            foreach ($fileModel->getErrors() as $errorModel) {
                $message = $errorModel->getPluginName() . ': ' . $errorModel->getMessage();
                $output .= '  <error';
                $output .= ' line="' . self::escape((string) $errorModel->getLine()) . '"';
                $output .= ' column="1"';
                $output .= ' severity="error"';
                $output .= ' message="' . self::escape($message) . '"';
                $output .= ' />' . "\n";
            }
            $output .= '</file>' . "\n";
        }

        IOHandler::$io->write('<?xml version="1.0" encoding="UTF-8"?>' . "\n");
        IOHandler::$io->write('<checkstyle>' . "\n");
        if ($output) {
            IOHandler::$io->write($output);
        }
        IOHandler::$io->write('</checkstyle>' . "\n");

        return !empty($model->getFiles());
    }

    /**
     * @param PluginOutputModel[] $outputModels
     *
     * @return PluginOutputModel
     */
    private static function combineModels(array $outputModels): PluginOutputModel
    {
        $resultModel = new PluginOutputModel();
        foreach ($outputModels as $model) {
            foreach ($model->getFiles() as $filePath => $fileModel) {
                $resultModel->appendFileIfNotExists($filePath);
                $resultFiles = $resultModel->getFiles();
                $resultFiles[$filePath]->setErrors(array_merge($resultFiles[$filePath]->getErrors(),
                                                               $fileModel->getErrors()));
                $resultModel->setFiles($resultFiles);
            }
        }

        $resultFiles = $resultModel->getFiles();
        foreach ($resultFiles as $fileModel) {
            $errors = $fileModel->getErrors();
            usort($errors, function (ErrorModel $a, ErrorModel $b) {
                if ($a->getLine() == $b->getLine()) {
                    if ($a->getPluginName() == $b->getPluginName()) {
                        return strcmp($a->getMessage(), $b->getMessage());
                    }

                    return strcmp($a->getPluginName(), $b->getPluginName());
                }

                return $a->getLine() - $b->getLine();
            });
            $errors = array_unique($errors, SORT_REGULAR);
            $fileModel->setErrors($errors);
        }
        $resultModel->setFiles($resultFiles);

        return $resultModel;
    }

    /**
     * Escapes values for using in XML.
     *
     * @param string $string
     *
     * @return string
     */
    private static function escape(string $string): string
    {
        return htmlspecialchars($string, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }
}