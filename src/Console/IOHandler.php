<?php

namespace Phalyfusion\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class IOHandler.
 */
class IOHandler
{
    /**
     * @var InputInterface
     */
    public static $input;

    /**
     * @var OutputInterface
     */
    public static $output;

    /**
     * @var SymfonyStyle
     */
    public static $io;

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    public static function initialize(InputInterface $input, OutputInterface $output): void
    {
        self::$input  = $input;
        self::$output = $output;
        self::$output->setDecorated(!self::$input->getOption('no-ansi'));
        self::$io = new SymfonyStyle($input, $output);
    }

    /**
     * Writes a message to the stderr and adds a newline at the end if -v flag passed.
     *
     * @param string|iterable $messages The message as an iterable of strings or a single string
     * @param bool            $newline  Whether to add a newline
     */
    public static function debug($messages, bool $newline = true): void
    {
        self::$io->getErrorStyle()->write($messages, $newline, OutputInterface::VERBOSITY_VERBOSE);
    }

    /**
     * @param string $message
     * @param string $error
     */
    public static function error(string $message, string $error = ''): void
    {
        self::$io->getErrorStyle()->writeln(["<error>{$message}</error>", $error]);
    }
}