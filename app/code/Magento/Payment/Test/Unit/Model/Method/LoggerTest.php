<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Payment\Test\Unit\Model\Method;

use Magento\Payment\Model\Method\Logger;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class LoggerTest extends TestCase
{
    /** @var Logger|MockObject */
    private $logger;

    /** @var LoggerInterface|MockObject */
    private $loggerMock;

    protected function setUp(): void
    {
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->logger = new Logger($this->loggerMock);
    }

    public function testDebugOn()
    {
        $debugData =
            [
                'request' => ['masked' => '123', 'unmasked' => '123']
            ];
        $expectedDebugData =
            [
                'request' => ['masked' => Logger::DEBUG_KEYS_MASK, 'unmasked' => '123']
            ];
        $debugReplaceKeys =
            [
                'masked'
            ];

        $this->loggerMock->expects($this->once())
            ->method('debug')
            ->with(var_export($expectedDebugData, true));

        $this->logger->debug($debugData, $debugReplaceKeys, true);
    }

    public function testDebugOnNoReplaceKeys()
    {
        $debugData =
            [
                'request' => ['data1' => '123', 'data2' => '123']
            ];

        $this->loggerMock->expects(static::once())
            ->method('debug')
            ->with(var_export($debugData, true));

        $this->logger->debug($debugData, [], true);
    }

    public function testDebugOff()
    {
        $debugData =
            [
                'request' => ['masked' => '123', 'unmasked' => '123']
            ];
        $debugReplaceKeys =
            [
                'masked'
            ];

        $this->loggerMock->expects($this->never())
            ->method('debug');

        $this->logger->debug($debugData, $debugReplaceKeys, false);
    }
}
