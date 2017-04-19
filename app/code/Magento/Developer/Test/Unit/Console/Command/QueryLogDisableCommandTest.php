<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Developer\Test\Unit\Console\Command;

use Magento\Developer\Console\Command\QueryLogDisableCommand;
use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\DB\Logger\LoggerProxy;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class QueryLogDisableCommandTest
 *
 * Tests dev:query-log:disable command.
 * Tests that the correct configuration is passed to the deployment config writer.
 */
class QueryLogDisableCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\DeploymentConfig\Writer
     */
    private $configWriter;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Developer\Console\Command\QueryLogEnableCommand
     */
    private $command;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->configWriter = $this->getMockBuilder(Writer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->command = new QueryLogDisableCommand($this->configWriter);
    }

    /**
     * Test execute()
     */
    public function testExecute()
    {
        $data = [LoggerProxy::PARAM_ALIAS => LoggerProxy::LOGGER_ALIAS_DISABLED];

        $this->configWriter
            ->expects($this->once())
            ->method('saveConfig')
            ->with([ConfigFilePool::APP_ENV => [LoggerProxy::CONF_GROUP_NAME => $data]]);

        $commandTester = new CommandTester($this->command);
        $commandTester->execute([]);
        $this->assertSame(
            QueryLogDisableCommand::SUCCESS_MESSAGE . PHP_EOL,
            $commandTester->getDisplay()
        );
    }
}
