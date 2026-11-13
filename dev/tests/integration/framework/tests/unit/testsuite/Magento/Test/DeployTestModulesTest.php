<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Test;

use Magento\TestFramework\Bootstrap\Settings;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\InputStream;
use Symfony\Component\Process\Process;

class DeployTestModulesTest extends TestCase
{
    /**
     * @var string
     */
    private string $directory;

    /**
     * @var array<int, array{Process, InputStream}>
     */
    private array $processes = [];

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir() . '/test-module-deployment-' . bin2hex(random_bytes(8));
        $filesystem = new Filesystem();
        $filesystem->mkdir([
            $this->directory . '/dev/tests/integration/framework',
            $this->directory . '/dev/tests/integration/_files/Magento/TestModuleConcurrent/etc',
            $this->directory . '/app/code/Magento/Example/Test/_files/Magento/TestModuleLocal/etc',
            $this->directory . '/vendor/magento/module-example/Test/_files/Magento/TestModuleVendor/etc',
        ]);
        file_put_contents($this->source(), '<config>original</config>');
        file_put_contents(
            $this->directory . '/app/code/Magento/Example/Test/_files/Magento/TestModuleLocal/etc/module.xml',
            '<config>local</config>'
        );
        file_put_contents(
            $this->directory . '/vendor/magento/module-example/Test/_files/Magento/TestModuleVendor/etc/module.xml',
            '<config>vendor</config>'
        );
        file_put_contents(
            $this->directory . '/dev/tests/integration/_files/Magento/TestModuleConcurrent/registration.php',
            '<?php file_put_contents(__DIR__ . "/registered", "registered");'
        );
    }

    protected function tearDown(): void
    {
        foreach ($this->processes as [$process, $input]) {
            $input->close();
            $process->stop();
        }
        (new Filesystem())->remove($this->directory);
    }

    public function testSequentialRunRemovesAllDeployedModules(): void
    {
        $process = $this->startProcess();
        $this->assertReady($process);
        $this->assertFileExists($this->destination());
        $this->assertFileExists($this->moduleDirectory() . '/TestModuleLocal/etc/module.xml');
        $this->assertFileExists($this->moduleDirectory() . '/TestModuleVendor/etc/module.xml');
        $this->assertFileExists($this->moduleDirectory() . '/TestModuleConcurrent/registered');
        $this->finishProcess($process);
        $this->assertDirectoryDoesNotExist($this->moduleDirectory() . '/TestModuleConcurrent');
        $this->assertDirectoryDoesNotExist($this->moduleDirectory() . '/TestModuleLocal');
        $this->assertDirectoryDoesNotExist($this->moduleDirectory() . '/TestModuleVendor');
        $this->assertFileExists($this->directory . '/dev/tests/integration/tmp/test-modules.lock');
        $this->assertFileExists($this->source());
    }

    public function testOverlappingRunsKeepModulesUntilLastExit(): void
    {
        $first = $this->startProcess();
        $this->assertReady($first);
        $before = stat($this->destination());
        $second = $this->startProcess();
        $this->assertReady($second);
        clearstatcache(true, $this->destination());
        $after = stat($this->destination());
        $this->assertSame($before['ino'], $after['ino']);
        $this->assertSame($before['mtime'], $after['mtime']);
        $this->finishProcess($first);
        $this->assertFileExists($this->destination());
        $this->assertFileExists($this->moduleDirectory() . '/TestModuleLocal/etc/module.xml');
        $this->assertFileExists($this->moduleDirectory() . '/TestModuleVendor/etc/module.xml');
        $this->finishProcess($second);
        $this->assertDirectoryDoesNotExist($this->moduleDirectory() . '/TestModuleConcurrent');
    }

    public function testParallelRunKeepsModulesAtExit(): void
    {
        $process = $this->startProcess(true);
        $this->assertReady($process);
        $this->finishProcess($process);
        $this->assertFileExists($this->destination());
        $this->assertFileExists($this->moduleDirectory() . '/TestModuleLocal/etc/module.xml');
        $this->assertFileExists($this->moduleDirectory() . '/TestModuleVendor/etc/module.xml');
    }

    public function testDefaultRunCannotDeleteModulesUsedByParallelRun(): void
    {
        $first = $this->startProcess();
        $this->assertReady($first);
        $second = $this->startProcess(true);
        $this->assertReady($second);
        $this->finishProcess($first);
        $this->assertFileExists($this->destination());
        $this->finishProcess($second);
        $this->assertFileExists($this->destination());
    }

    public function testChangedContentWithSameMtimeIsReplacedAtomically(): void
    {
        $first = $this->startProcess(true);
        $this->assertReady($first);
        $this->finishProcess($first);
        $oldFile = fopen($this->destination(), 'r');
        try {
            $mtime = filemtime($this->source());
            file_put_contents($this->source(), '<config>changed</config>');
            touch($this->source(), $mtime);
            $second = $this->startProcess(true);
            $this->assertReady($second);
            $this->assertSame('<config>original</config>', stream_get_contents($oldFile));
            $this->assertSame('<config>changed</config>', file_get_contents($this->destination()));
            $this->assertSame([], glob(dirname($this->destination()) . '/.test-module-*'));
            $this->finishProcess($second);
        } finally {
            fclose($oldFile);
        }
    }

    public function testChangedDeploymentWaitsForActiveReader(): void
    {
        $first = $this->startProcess();
        $this->assertReady($first);
        file_put_contents($this->source(), '<config>changed</config>');
        $second = $this->startProcess();
        usleep(100000);
        $this->assertTrue($this->processes[$second][0]->isRunning());
        $this->assertSame("starting\n", $this->processes[$second][0]->getOutput());
        $this->assertSame('<config>original</config>', file_get_contents($this->destination()));
        $this->finishProcess($first);
        $this->assertReady($second);
        $this->assertSame('<config>changed</config>', file_get_contents($this->destination()));
        $this->assertFileExists($this->moduleDirectory() . '/TestModuleLocal/etc/module.xml');
        $this->assertFileExists($this->moduleDirectory() . '/TestModuleVendor/etc/module.xml');
        $this->finishProcess($second);
        $this->assertFileDoesNotExist($this->destination());
    }

    private function startProcess(bool $parallel = false): int
    {
        $framework = dirname(__DIR__, 5);
        $script = 'require ' . var_export(dirname($framework, 4) . '/vendor/autoload.php', true) . ';'
            . 'require ' . var_export($framework . '/Magento/TestFramework/Bootstrap/Settings.php', true) . ';'
            . '$testFrameworkDir = ' . var_export($this->directory . '/dev/tests/integration/framework', true) . ';'
            . '$settings = new ' . Settings::class . '(dirname($testFrameworkDir), '
            . var_export(['TESTS_PARALLEL_RUN' => (int)$parallel], true) . ');'
            . 'fwrite(STDOUT, "starting\n");'
            . 'require ' . var_export($framework . '/deployTestModules.php', true) . ';'
            . 'fwrite(STDOUT, "ready\n"); fgets(STDIN);';
        $input = new InputStream();
        $process = new Process([PHP_BINARY, '-r', $script]);
        $process->setInput($input);
        $process->setTimeout(10);
        $this->processes[] = [$process, $input];
        $process->start();
        $this->waitForOutput($process, "starting\n");
        $this->assertSame("starting\n", substr($process->getOutput(), 0, strlen("starting\n")));
        return array_key_last($this->processes);
    }

    private function assertReady(int $index): void
    {
        [$process] = $this->processes[$index];
        $this->waitForOutput($process, "ready\n");
        $this->assertSame("starting\nready\n", $process->getOutput());
        $process->clearOutput();
    }

    private function waitForOutput(Process $process, string $output): void
    {
        while (!str_contains($process->getOutput(), $output) && $process->isRunning()) {
            $process->checkTimeout();
            usleep(1000);
        }
    }

    private function finishProcess(int $index): void
    {
        [$process, $input] = $this->processes[$index];
        $input->write("exit\n");
        $input->close();
        $status = $process->wait();
        unset($this->processes[$index]);
        $this->assertSame('', $process->getOutput());
        $this->assertSame('', $process->getErrorOutput());
        $this->assertSame(0, $status);
    }

    private function source(): string
    {
        return $this->directory . '/dev/tests/integration/_files/Magento/TestModuleConcurrent/etc/module.xml';
    }

    private function moduleDirectory(): string
    {
        return $this->directory . '/app/code/Magento';
    }

    private function destination(): string
    {
        return $this->moduleDirectory() . '/TestModuleConcurrent/etc/module.xml';
    }
}
