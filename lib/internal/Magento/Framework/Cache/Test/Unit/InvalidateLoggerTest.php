<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * \Magento\Framework\Cache\InvalidateLogger test case
 */
namespace Magento\Framework\Cache\Test\Unit;

class InvalidateLoggerTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject | \Magento\Framework\App\Request\Http */
    protected $requestMock;

    /** @var \PHPUnit\Framework\MockObject\MockObject | \Psr\Log\LoggerInterface */
    protected $loggerMock;

    /** @var \PHPUnit\Framework\MockObject\MockObject | \Magento\Framework\Cache\InvalidateLogger */
    protected $invalidateLogger;

    /** @var string */
    protected $method = 'GET';

    /** @var string */
    protected $url = 'http://website.com/home';

    /** @var array */
    protected $params = ['param1', 'param2'];

    protected function setUp(): void
    {
        $this->requestMock = $this->createMock(\Magento\Framework\App\Request\Http::class);
        $this->loggerMock = $this->createMock(\Psr\Log\LoggerInterface::class);
        $this->invalidateLogger = new \Magento\Framework\Cache\InvalidateLogger(
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
