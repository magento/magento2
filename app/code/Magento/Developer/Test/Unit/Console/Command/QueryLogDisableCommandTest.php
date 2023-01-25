<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Developer\Test\Unit\Console\Command;

use Magento\Developer\Console\Command\QueryLogDisableCommand;
use Magento\Developer\Console\Command\QueryLogEnableCommand;
use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\DB\Logger\LoggerProxy;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class QueryLogDisableCommandTest
 *
 * Tests dev:query-log:disable command.
 * Tests that the correct configuration is passed to the deployment config writer.
 */
class QueryLogDisableCommandTest extends TestCase
{
    /**
     * @var MockObject|Writer
     */
    private $configWriter;

    /**
     * @var MockObject|QueryLogEnableCommand
     */
    private $command;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
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
