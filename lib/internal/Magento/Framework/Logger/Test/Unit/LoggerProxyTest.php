<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Logger\Test\Unit;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Logger\LoggerProxy;
use Magento\Framework\Logger\Monolog;
use Magento\Framework\ObjectManagerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class LoggerProxyTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return array
     */
    public static function methodsList()
    {
        return [
            [LogLevel::EMERGENCY],
            [LogLevel::ALERT],
            [LogLevel::CRITICAL],
            [LogLevel::ERROR],
            [LogLevel::WARNING],
            [LogLevel::NOTICE],
            [LogLevel::INFO],
            [LogLevel::DEBUG],
        ];
    }

    /**
     * @test
     * @dataProvider methodsList
     * @param $method
     */
    public function logMessage($method)
    {
        $deploymentConfig = $this->getMockBuilder(DeploymentConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManager = $this->getMockBuilder(ObjectManagerInterface::class)
            ->getMockForAbstractClass();

        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->getMockForAbstractClass();

        $objectManager->expects($this->at(0))
            ->method('get')
            ->with(DeploymentConfig::class)
            ->willReturn($deploymentConfig);

        $objectManager->expects($this->at(1))
            ->method('get')
            ->with(Monolog::class)
            ->willReturn($logger);
        $logger->expects($this->once())->method($method)->with('test');

        $loggerProxy = new LoggerProxy($objectManager);
        $loggerProxy->$method('test');
    }

    /**
     * @test
     */
    public function createWithArguments()
    {
        $deploymentConfig = $this->getMockBuilder(DeploymentConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManager = $this->getMockBuilder(ObjectManagerInterface::class)
            ->getMockForAbstractClass();

        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->getMockForAbstractClass();

        $args = ['name' => 'test'];
        $deploymentConfig->expects($this->at(1))
            ->method('get')
            ->with('log/args')
            ->willReturn($args);

        $objectManager->expects($this->at(0))
            ->method('get')
            ->with(DeploymentConfig::class)
            ->willReturn($deploymentConfig);

        $objectManager->expects($this->once())
            ->method('create')
            ->with(Monolog::class, $args)
            ->willReturn($logger);
        $logger->expects($this->once())->method('log')->with(LogLevel::ALERT, 'test');

        $loggerProxy = new LoggerProxy($objectManager);
        $loggerProxy->log(LogLevel::ALERT, 'test');
    }
}
