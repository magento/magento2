<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Tools\Console\Command;

use Magento\Tools\Console\Command\DependenciesShowModulesCommand;
use Symfony\Component\Console\Tester\CommandTester;

class DependenciesShowModulesCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DependenciesShowModulesCommand
     */
    private $command;

    /**
     * @var CommandTester
     */
    private $commandTester;

    public function setUp()
    {
        $this->command = new DependenciesShowModulesCommand();
        $this->commandTester = new CommandTester($this->command);
    }

    public function tearDown()
    {
        if (file_exists(__DIR__ . '/_files/output/modules.csv')) {
            unlink(__DIR__ . '/_files/output/modules.csv');
        }
    }

    public function testExecute()
    {
        $this->commandTester->execute(
            ['--directory' => __DIR__ . '/_files/root', '--output' => __DIR__ . '/_files/output/modules.csv']
        );
        $this->assertEquals('Report successfully processed.' . PHP_EOL, $this->commandTester->getDisplay());
        $this->assertFileEquals(
            __DIR__ . '/_files/expected/modules.csv',
            __DIR__ . '/_files/output/modules.csv'
        );
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
