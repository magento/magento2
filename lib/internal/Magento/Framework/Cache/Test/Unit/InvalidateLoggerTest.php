<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * \Magento\Framework\Cache\InvalidateLogger test case
 */
namespace Magento\Framework\Cache\Test\Unit;

use Magento\Framework\App\Request\Http;
use Magento\Framework\Cache\InvalidateLogger;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class InvalidateLoggerTest extends TestCase
{
    /** @var MockObject|Http */
    protected $requestMock;

    /** @var MockObject|LoggerInterface */
    protected $loggerMock;

    /** @var MockObject|InvalidateLogger */
    protected $invalidateLogger;

    /** @var string */
    protected $method = 'GET';

    /** @var string */
    protected $url = 'http://website.com/home';

    /** @var array */
    protected $params = ['param1', 'param2'];

    protected function setUp(): void
    {
        $this->requestMock = $this->createMock(Http::class);
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->invalidateLogger = new InvalidateLogger(
            $this->requestMock,
            $this->loggerMock
        );
        $this->requestMock->expects($this->once())
            ->method('getMethod')
            ->willReturn($this->method);
        $this->requestMock->expects($this->once())
            ->method('getUriString')
            ->willReturn($this->url);
    }

    public function testCritical()
    {
        $this->loggerMock->expects($this->once())
            ->method('critical')
            ->with('message', ['method' => $this->method, 'url' => $this->url, 'invalidateInfo' => $this->params]);
        $this->invalidateLogger->critical('message', $this->params);
    }

    public function testExecute()
    {
        $this->loggerMock->expects($this->once())
            ->method('debug')
            ->with(
                'cache_invalidate: ',
                ['method' => $this->method, 'url' => $this->url, 'invalidateInfo' => $this->params]
            );
        $this->invalidateLogger->execute($this->params);
    }

    public function testMakeParams()
    {
        $expected = ['method' => $this->method, 'url' => $this->url, 'invalidateInfo' => $this->params];
        $method = new \ReflectionMethod($this->invalidateLogger, 'makeParams');
        $method->setAccessible(true);
        $this->assertEquals(
            $expected,
            $method->invoke($this->invalidateLogger, $this->params)
        );
    }

    protected function tearDown(): void
    {
        unset($this->requestMock);
        unset($this->loggerMock);
    }
}
