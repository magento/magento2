<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Logger\Test\Unit;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Logger\LoggerProxy;
use Magento\Framework\Logger\Monolog;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class LoggerProxyTest extends TestCase
{
    /**
     * @return array
     */
    public static function methodsList(): array
    {
        return [
            [LogLevel::EMERGENCY],
            [LogLevel::ALERT],
            [LogLevel::CRITICAL],
            [LogLevel::ERROR],
            [LogLevel::WARNING],
            [LogLevel::NOTICE],
            [LogLevel::INFO],
            [LogLevel::DEBUG]
        ];
    }

    /**
     * @test
     *
     * @param $method
     *
     * @return void
     * @dataProvider methodsList
     */
    public function logMessage($method): void
    {
        $deploymentConfig = $this->getMockBuilder(DeploymentConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManager = $this->getMockBuilder(ObjectManagerInterface::class)
            ->getMockForAbstractClass();

        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->getMockForAbstractClass();

        $objectManager
            ->method('get')
            ->withConsecutive([DeploymentConfig::class], [Monolog::class])
            ->willReturnOnConsecutiveCalls($deploymentConfig, $logger);
        $logger->expects($this->once())->method($method)->with('test');

        $loggerProxy = new LoggerProxy($objectManager);
        $loggerProxy->$method('test');
    }

    /**
     * @test
     *
     * @return void
     */
    public function createWithArguments(): void
    {
        $deploymentConfig = $this->getMockBuilder(DeploymentConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManager = $this->getMockBuilder(ObjectManagerInterface::class)
            ->getMockForAbstractClass();

        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->getMockForAbstractClass();

        $args = ['name' => 'test'];
        $deploymentConfig
            ->method('get')
            ->withConsecutive([], ['log/args'])
            ->willReturnOnConsecutiveCalls(null, $args);

        $objectManager
            ->method('get')
            ->withConsecutive([DeploymentConfig::class])
            ->willReturnOnConsecutiveCalls($deploymentConfig);

        $objectManager->expects($this->once())
            ->method('create')
            ->with(Monolog::class, $args)
            ->willReturn($logger);
        $logger->expects($this->once())->method('log')->with(LogLevel::ALERT, 'test');

        $loggerProxy = new LoggerProxy($objectManager);
        $loggerProxy->log(LogLevel::ALERT, 'test');
    }
}
