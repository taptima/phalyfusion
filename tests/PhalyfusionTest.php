<?php

use Symfony\Component\Process\Process;
use PHPUnit\Framework\TestCase;

class PhalyfusionTest extends TestCase
{
    public function testPhalyfusionNoErrors()
    {
        $runCommand = '../phalyfusion analyse -c configuration/phalyfusion_test.neon sampleCodebase/sampleNoErrors.php --no-ansi';
        $process = Process::fromShellCommandline($runCommand, __DIR__);
        $process->run();
        $this->assertSuccessful($process);
        $this->assertStringEqualsFile(__DIR__ . '/expectedOutputs/noErrorsText.txt',$process->getOutput());

        $runCommand = '../phalyfusion analyse -c configuration/phalyfusion_test.neon sampleCodebase/sampleNoErrors.php -f json';
        $process = Process::fromShellCommandline($runCommand, __DIR__);
        $process->run();
        $this->assertSuccessful($process);
        $this->assertStringEqualsFile(__DIR__ . '/expectedOutputs/noErrorsJson.txt',$process->getOutput());

        $runCommand = '../phalyfusion analyse -c configuration/phalyfusion_test.neon sampleCodebase/sampleNoErrors.php -f checkstyle';
        $process = Process::fromShellCommandline($runCommand, __DIR__);
        $process->run();
        $this->assertSuccessful($process);
        $this->assertStringEqualsFile(__DIR__ . '/expectedOutputs/noErrorsCheckstyle.txt',$process->getOutput());
    }

    public function testPhalyfusion()
    {
        $runCommand = '../phalyfusion analyse -c configuration/phalyfusion_test.neon --no-ansi';
        $process = Process::fromShellCommandline($runCommand, __DIR__);
        $process->run();
        $this->assertSuccessful($process);
        $this->assertStringEqualsFile(__DIR__ . '/expectedOutputs/errorsText.txt',$process->getOutput());

        $runCommand = '../phalyfusion analyse -c configuration/phalyfusion_test.neon -f json';
        $process = Process::fromShellCommandline($runCommand, __DIR__);
        $process->run();
        $this->assertSuccessful($process);
        $this->assertStringEqualsFile(__DIR__ . '/expectedOutputs/errorsJson.txt',$process->getOutput());

        $runCommand = '../phalyfusion analyse -c configuration/phalyfusion_test.neon -f checkstyle';
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
