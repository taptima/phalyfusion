<?php

namespace Phalyfusion\Console;

use Phalyfusion\Model\ErrorModel;
use Phalyfusion\Model\PluginOutputModel;

/**
 * Class OutputGenerator.
 */
class OutputGenerator
{
    /**
     * @param PluginOutputModel[] $outputModels
     *
     * @return int
     */
    public static function tableOutput(array $outputModels): int
    {
        $model      = self::combineModels($outputModels);
        $errorCount = 0;

        IOHandler::$io->title(' ~~ Phalyfusion! ~~ ');
        if (!$model->getFiles()) {
            IOHandler::$io->success('No errors found!');

            return 0;
        }

        foreach ($model->getFiles() as $fileModel) {
            $rows = [];
            foreach ($fileModel->getErrors() as $errorModel) {
                $rows[] = [$errorModel->getLine(), $errorModel->getPluginName(), $errorModel->getMessage()];
                ++$errorCount;
            }
            IOHandler::$io->table(['Line', 'Plugin', $fileModel->getPath()], $rows);
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