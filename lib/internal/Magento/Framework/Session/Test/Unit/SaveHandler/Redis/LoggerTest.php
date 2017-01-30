<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Session\Test\Unit\SaveHandler\Redis;

use Cm\RedisSession\Handler\LoggerInterface;
use Magento\Framework\Session\SaveHandler\Redis\Logger;

class LoggerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Cm\RedisSession\Handler\ConfigInterface
     */
    protected $config;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $psrLogger;

    /**
     * @var \Magento\Framework\Session\SaveHandler\Redis\Logger
     */
    protected $logger;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var string
     */
    protected $requestUri = 'customer/account/login';

    public function setUp()
    {
        $this->config = $this->getMock('Cm\RedisSession\Handler\ConfigInterface', [], [], '', false);
        $this->config->expects($this->once())
            ->method('getLogLevel')
            ->willReturn(LoggerInterface::DEBUG);
        $this->psrLogger = $this->getMock('Psr\Log\LoggerInterface', [], [], '', false);
        $this->request = $this->getMock('Magento\Framework\App\Request\Http', [], [], '', false);
        //$this->logger = new Logger($this->config, $this->psrLogger, $this->request);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->logger = $objectManager->getObject(
            'Magento\Framework\Session\SaveHandler\Redis\Logger',
            [
                'config' => $this->config,
                'logger' => $this->psrLogger,
                'request' => $this->request
            ]
        );
    }

    /**
     * @dataProvider logDataProvider
     */
    public function testLog($logLevel, $method)
    {
        $message = 'Error message';
        $this->request->expects($this->once())
            ->method('getRequestUri')
            ->willReturn($this->requestUri);
        $this->psrLogger->expects($this->once())
            ->method($method)
            ->with($message . ' ' . $this->requestUri);
        $this->logger->log($message, $logLevel);
    }

    public function logDataProvider()
    {
        return [
            [LoggerInterface::EMERGENCY, 'emergency'],
            [LoggerInterface::ALERT, 'alert'],
            [LoggerInterface::CRITICAL, 'critical'],
            [LoggerInterface::ERROR, 'error'],
            [LoggerInterface::WARNING, 'warning'],
            [LoggerInterface::NOTICE, 'notice'],
            [LoggerInterface::INFO, 'info'],
            [LoggerInterface::DEBUG, 'debug'],
        ];
    }

    public function testLogException()
    {
        $exception = new \Exception('Error message');
        $this->psrLogger->expects($this->once())
            ->method('critical')
            ->with($exception->getMessage());
        $this->logger->logException($exception);
    }
}
