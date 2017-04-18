<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Developer\Test\Unit\Console\Command;

use Magento\Developer\Console\Command\QueryLogEnableCommand;
use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\DB\Logger\LoggerProxy;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class QueryLogEnableCommandTest
 *
 * Tests dev:query-log:enable command.
 * Tests that the correct configuration is passed to the deployment config writer with and without parameters.
 */
class QueryLogEnableCommandTest extends \PHPUnit_Framework_TestCase
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
        $this->command = new QueryLogEnableCommand($this->configWriter);
    }

    /**
     * Test execute() without parameters.
     */
    public function testExecuteWithNoParams()
    {
        $data = [LoggerProxy::PARAM_ALIAS => LoggerProxy::LOGGER_ALIAS_FILE];
        $data[LoggerProxy::PARAM_LOG_ALL] = 1;
        $data[LoggerProxy::PARAM_QUERY_TIME] = 0.001;
        $data[LoggerProxy::PARAM_CALL_STACK] = 1;

        $this->configWriter = $this->getMockBuilder(Writer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configWriter
            ->expects($this->any())
            ->method('saveConfig')
            ->with([ConfigFilePool::APP_ENV => [LoggerProxy::CONF_GROUP_NAME => $data]]);

        $commandTester = new CommandTester($this->command);
        $commandTester->execute([]);
        $this->assertSame(
            QueryLogEnableCommand::SUCCESS_MESSAGE . PHP_EOL,
            $commandTester->getDisplay()
        );
    }

    /**
     * Test execute() with parameters.
     */
    public function testExecuteWithParams()
    {
        $data = [LoggerProxy::PARAM_ALIAS => LoggerProxy::LOGGER_ALIAS_FILE];
        $data[LoggerProxy::PARAM_LOG_ALL] = 0;
        $data[LoggerProxy::PARAM_QUERY_TIME] = '0.05';
        $data[LoggerProxy::PARAM_CALL_STACK] = 0;

        $this->configWriter = $this->getMockBuilder(Writer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configWriter
            ->expects($this->any())
            ->method('saveConfig')
            ->with([ConfigFilePool::APP_ENV => [LoggerProxy::CONF_GROUP_NAME => $data]]);

        $commandTester = new CommandTester($this->command);
        $commandTester->execute(
            [
                '--include-all-queries' => 'false',
                '--include-call-stack' => 'false',
                '--query-time-threshold' => '0.05',
            ]
        );
        $this->assertSame(
            QueryLogEnableCommand::SUCCESS_MESSAGE . PHP_EOL,
            $commandTester->getDisplay()
        );
    }
}
