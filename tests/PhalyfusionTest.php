<?php

use Symfony\Component\Process\Process;
use PHPUnit\Framework\TestCase;

class PhalyfusionTest extends TestCase
{
    public function testPhalyfusionNoErrorsNoAnsi(): void
    {
        $runCommand = '../phalyfusion analyse -c configuration/phalyfusion_test.neon sampleCodebase/sampleNoErrors.php --no-ansi -p';
        $process = Process::fromShellCommandline($runCommand, __DIR__);
        $process->run();
        $this->assertSuccessful($process);
        $this->assertStringEqualsFile(__DIR__ . '/expectedOutputs/noErrorsText.txt', $process->getOutput());
    }

    public function testPhalyfusionNoErrorsJsonFormat(): void
    {
        $runCommand = '../phalyfusion analyse -c configuration/phalyfusion_test.neon sampleCodebase/sampleNoErrors.php -f json -p';
        $process = Process::fromShellCommandline($runCommand, __DIR__);
        $process->run();
        $this->assertSuccessful($process);
        $this->assertStringEqualsFile(__DIR__ . '/expectedOutputs/noErrorsJson.txt',$process->getOutput());
    }

    public function testPhalyfusionNoErrorsCheckstyleFormat(): void
    {
        $runCommand = '../phalyfusion analyse -c configuration/phalyfusion_test.neon sampleCodebase/sampleNoErrors.php -f checkstyle -p';
        $process = Process::fromShellCommandline($runCommand, __DIR__);
        $process->run();
        $this->assertSuccessful($process);
        $this->assertStringEqualsFile(__DIR__ . '/expectedOutputs/noErrorsCheckstyle.txt',$process->getOutput());
    }

    public function testPhalyfusionNoAnsi(): void
    {
        $runCommand = '../phalyfusion analyse -c configuration/phalyfusion_test.neon --no-ansi -p';
        $process = Process::fromShellCommandline($runCommand, __DIR__);
        $process->run();
        $this->assertSuccessful($process);
        $this->assertStringEqualsFile(__DIR__ . '/expectedOutputs/errorsText.txt', $process->getOutput());
    }

    public function testPhalyfusionJsonFormat(): void
    {
        $runCommand = '../phalyfusion analyse -c configuration/phalyfusion_test.neon -f json -p';
        $process = Process::fromShellCommandline($runCommand, __DIR__);
        $process->run();
        $this->assertSuccessful($process);
        $this->assertStringEqualsFile(__DIR__ . '/expectedOutputs/errorsJson.txt',$process->getOutput());
    }

    public function testPhalyfusionCheckStyleFormat(): void
    {
        $runCommand = '../phalyfusion analyse -c configuration/phalyfusion_test.neon -f checkstyle -p';
        $process = Process::fromShellCommandline($runCommand, __DIR__);
        $process->run();
        $this->assertSuccessful($process);
        $this->assertStringEqualsFile(__DIR__ . '/expectedOutputs/errorsCheckstyle.txt',$process->getOutput());

    }

    private function assertSuccessful(Process $process): void
    {
        $this->assertSame(
            0,
            $process->getExitCode(),
            "\"{$process->getCommandLine()}\" returned code {$process->getExitCode()}. Error output: \n\n{$process->getErrorOutput()}"
        );
    }
}
