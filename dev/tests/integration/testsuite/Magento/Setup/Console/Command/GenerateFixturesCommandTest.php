<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Console\Command;

use Magento\Framework\Console\Cli;
use Magento\Setup\Console\Command\GenerateFixturesCommand;
use Magento\TestFramework\Helper\Bootstrap;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class GenerateFixturesCommandCommandTest
 * @package Magento\Setup\Console\Command
 */
class GenerateFixturesCommandTest extends \PHPUnit\Framework\TestCase
{
    private $objectManager;
    /**
     * @var GenerateFixturesCommand
     */
    private $command;

    /**
     * @var CommandTester
     */
    private $commandTester;

    /**
     * Setup
     */
    public function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->command = $this->objectManager->create(GenerateFixturesCommand::class);
        $this->commandTester = new CommandTester($this->command);
    }

    public function testExecute()
    {
        $this->commandTester->execute(
            [GenerateFixturesCommand::PROFILE_ARGUMENT => '/var/www/magento2ce/setup/performance-toolkit/profiles/ce/small.xml']
        );

        $this->assertEquals(Cli::RETURN_SUCCESS, $this->commandTester->getStatusCode(), $this->commandTester->getDisplay());
    }
}
