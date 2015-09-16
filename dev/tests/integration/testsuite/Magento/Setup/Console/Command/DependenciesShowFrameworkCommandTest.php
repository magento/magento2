<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Console\Command;

use Magento\Framework\Component\ComponentRegistrar;
use Symfony\Component\Console\Tester\CommandTester;

class DependenciesShowFrameworkCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DependenciesShowFrameworkCommand
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
        $directoryList = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Framework\App\Filesystem\DirectoryList', ['root' => BP]);
        $this->command = new DependenciesShowFrameworkCommand($directoryList, new ComponentRegistrar());
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
        if (file_exists(__DIR__ . '/_files/output/framework.csv')) {
            unlink(__DIR__ . '/_files/output/framework.csv');
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
            ['--output' => __DIR__ . '/_files/output/framework.csv']
        );
        $this->assertEquals('Report successfully processed.' . PHP_EOL, $this->commandTester->getDisplay());
        $fileContents = file_get_contents(__DIR__ . '/_files/output/framework.csv');
        $this->assertContains(
            '"Dependencies of framework:","Total number"' . PHP_EOL . ',2' . PHP_EOL,
            $fileContents
        );
        $this->assertContains('"Dependencies for each module:",' . PHP_EOL, $fileContents);
        $this->assertContains('"Magento\A",1' . PHP_EOL . '" -- Magento\Framework",1' . PHP_EOL, $fileContents);
        $this->assertContains('"Magento\B",1' . PHP_EOL . '" -- Magento\Framework",1' . PHP_EOL, $fileContents);

    }
}
