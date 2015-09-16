<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Console\Command;

use Magento\Framework\Component\ComponentRegistrar;
use Symfony\Component\Console\Tester\CommandTester;

class DependenciesShowModulesCircularCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DependenciesShowModulesCircularCommand
     */
    private $command;

    /**
     * @var CommandTester
     */
    private $commandTester;

    /**
     * @var array
     */
    private $backupRegistrar;

    public function setUp()
    {
        $this->command = new DependenciesShowModulesCircularCommand();
        $this->commandTester = new CommandTester($this->command);
        $reflection = new \ReflectionClass('Magento\Framework\Component\ComponentRegistrar');
        $paths = $reflection->getProperty('paths');
        $paths->setAccessible(true);
        $this->backupRegistrar = $paths->getValue();
        $paths->setValue(
            [
                ComponentRegistrar::MODULE => [
                    'Magento_A' => __DIR__ . '/_files/root/app/code/Magento/A',
                    'Magento_B' => __DIR__ . '/_files/root/app/code/Magento/B'
                ]
            ]
        );
        $paths->setAccessible(false);
    }

    public function tearDown()
    {
        if (file_exists(__DIR__ . '/_files/output/circular.csv')) {
            unlink(__DIR__ . '/_files/output/circular.csv');
        }
        $reflection = new \ReflectionClass('Magento\Framework\Component\ComponentRegistrar');
        $paths = $reflection->getProperty('paths');
        $paths->setAccessible(true);
        $paths->setValue($this->backupRegistrar);
        $paths->setAccessible(false);
    }

    public function testExecute()
    {
        $this->commandTester->execute(
            ['--output' => __DIR__ . '/_files/output/circular.csv']
        );
        $this->assertEquals('Report successfully processed.' . PHP_EOL, $this->commandTester->getDisplay());
        $fileContents = file_get_contents(__DIR__ . '/_files/output/circular.csv');
        $this->assertContains(
            '"Circular dependencies:","Total number of chains"' . PHP_EOL . ',2' . PHP_EOL,
            $fileContents
        );
        $this->assertContains('"Circular dependencies for each module:",' . PHP_EOL, $fileContents);
        $this->assertContains(
            'magento/module-a,1' . PHP_EOL . 'magento/module-a->magento/module-b->magento/module-a' . PHP_EOL,
            $fileContents
        );
        $this->assertContains(
            'magento/module-b,1' . PHP_EOL . 'magento/module-b->magento/module-a->magento/module-b' . PHP_EOL,
            $fileContents
        );
    }
}
