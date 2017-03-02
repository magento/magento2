<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Payment\Test\Unit\Model\Method;

use Magento\Payment\Model\Method\Logger;
use Psr\Log\LoggerInterface;

class LoggerTest extends \PHPUnit_Framework_TestCase
{
    /** @var Logger | \PHPUnit_Framework_MockObject_MockObject */
    private $logger;

    /** @var LoggerInterface | \PHPUnit_Framework_MockObject_MockObject */
    private $loggerMock;

    protected function setUp()
    {
        $this->loggerMock = $this->getMockForAbstractClass(\Psr\Log\LoggerInterface::class);
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
