<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Session\Test\Unit\SaveHandler\Redis;

use Cm\RedisSession\Handler\ConfigInterface;
use Cm\RedisSession\Handler\LoggerInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Session\SaveHandler\Redis\Logger;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class LoggerTest extends TestCase
{
    /**
     * @var ConfigInterface
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
     * @var Http
     */
    protected $request;

    /**
     * @var string
     */
    protected $requestUri = 'customer/account/login';

    protected function setUp(): void
    {
        $this->config = $this->getMockForAbstractClass(ConfigInterface::class);
        $this->config->expects($this->once())
            ->method('getLogLevel')
            ->willReturn(LoggerInterface::DEBUG);
        $this->psrLogger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $this->request = $this->createMock(Http::class);
        //$this->logger = new Logger($this->config, $this->psrLogger, $this->request);
        $objectManager = new ObjectManager($this);
        $this->logger = $objectManager->getObject(
            Logger::class,
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

    /**
     * @return array
     */
    public static function logDataProvider()
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
