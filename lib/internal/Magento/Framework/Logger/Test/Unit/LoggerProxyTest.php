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
            ->willReturnCallback(
                function ($arg1) use ($deploymentConfig, $logger) {
                    if ($arg1 == DeploymentConfig::class) {
                        return $deploymentConfig;
                    } elseif ($arg1 == Monolog::class) {
                        return $logger;
                    }
                }
            );
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
            ->willReturnCallback(
                function ($arg1) use ($args) {
                    if (empty($arg1)) {
                        return null;
                    } elseif ($arg1 == 'log/args') {
                        return $args;
                    }
                }
            );

        $objectManager
            ->method('get')
            ->willReturnCallback(
                function ($arg1) use ($deploymentConfig) {
                    if ($arg1 == DeploymentConfig::class) {
                        return $deploymentConfig;
                    }
                }
            );

        $objectManager->expects($this->once())
            ->method('create')
            ->with(Monolog::class, $args)
            ->willReturn($logger);
        $logger->expects($this->once())->method('log')->with(LogLevel::ALERT, 'test');

        $loggerProxy = new LoggerProxy($objectManager);
        $loggerProxy->log(LogLevel::ALERT, 'test');
    }

    /**
     * @test
     *
     * @param $method
     *
     * @return void
     * @dataProvider methodsList
     */
    public function logException($method): void
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
            ->willReturnCallback(
                function ($arg1) use ($deploymentConfig, $logger) {
                    if ($arg1 == DeploymentConfig::class) {
                        return $deploymentConfig;
                    } elseif ($arg1 == Monolog::class) {
                        return $logger;
                    }
                }
            );

        $message = new \Exception('This is an exception.');

        $logger->expects($this->once())->method($method)->with($message);

        $loggerProxy = new LoggerProxy($objectManager);
        $loggerProxy->$method($message);
    }
}
