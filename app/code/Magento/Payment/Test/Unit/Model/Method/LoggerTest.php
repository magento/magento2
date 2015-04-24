<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Payment\Test\Unit\Model\Method;

use Magento\Payment\Model\Method\ConfigInterface;
use Magento\Payment\Model\Method\Logger;
use Psr\Log\LoggerInterface;

class LoggerTest extends \PHPUnit_Framework_TestCase
{
    /** @var Logger | \PHPUnit_Framework_MockObject_MockObject */
    private $logger;

    /** @var LoggerInterface | \PHPUnit_Framework_MockObject_MockObject */
    private $loggerMock;

    /** @var ConfigInterface | \PHPUnit_Framework_MockObject_MockObject */
    private $configMock;

    protected function setUp()
    {
        $this->loggerMock = $this->getMockForAbstractClass('Psr\Log\LoggerInterface');
        $this->configMock = $this->getMockForAbstractClass('Magento\Payment\Model\Method\ConfigInterface');

        $this->logger = new Logger($this->loggerMock);
    }

    public function testDebugOn()
    {
        $this->configMock->expects($this->once())
            ->method('getConfigValue')
            ->with('debug')
            ->willReturn(true);
        $this->loggerMock->expects($this->once())
            ->method('debug')
            ->with("''");

        $this->logger->debug('', $this->configMock);
    }

    public function testDebugOff()
    {
        $this->configMock->expects($this->once())
            ->method('getConfigValue')
            ->with('debug')
            ->willReturn(false);
        $this->loggerMock->expects($this->never())
            ->method('debug');

        $this->logger->debug('', $this->configMock);
    }
}
