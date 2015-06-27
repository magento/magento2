<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Console\Command;

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

    public function setUp()
    {
        $directoryList = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Framework\App\Filesystem\DirectoryList', ['root' => BP]);
        $this->command = new DependenciesShowFrameworkCommand($directoryList);
        $this->commandTester = new CommandTester($this->command);
    }

    public function tearDown()
    {
        if (file_exists(__DIR__ . '/_files/output/framework.csv')) {
            unlink(__DIR__ . '/_files/output/framework.csv');
        }
    }

    public function testExecute()
    {
        $this->commandTester->execute(
            ['--directory' => __DIR__ . '/_files/root', '--output' => __DIR__ . '/_files/output/framework.csv']
        );
        $this->assertEquals('Report successfully processed.' . PHP_EOL, $this->commandTester->getDisplay());
        $fileContents = file_get_contents(__DIR__ . '/_files/output/framework.csv');
        $this->assertContains(
            '"Dependencies of framework:","Total number"' . PHP_EOL . '"","2"' . PHP_EOL,
            $fileContents
        );
        $this->assertContains('"Dependencies for each module:",""' . PHP_EOL, $fileContents);
        $this->assertContains('"Magento\A","1"' . PHP_EOL . '" -- Magento\Framework","1"' . PHP_EOL, $fileContents);
        $this->assertContains('"Magento\B","1"' . PHP_EOL . '" -- Magento\Framework","1"' . PHP_EOL, $fileContents);

    }

    public function testExecuteInvalidDirectory()
    {
        $this->commandTester->execute(['--directory' => '/invalid/path']);
        $this->assertContains(
            'Please check the path you provided. Dependencies report generator failed with error:',
            $this->commandTester->getDisplay()
        );
    }
}
