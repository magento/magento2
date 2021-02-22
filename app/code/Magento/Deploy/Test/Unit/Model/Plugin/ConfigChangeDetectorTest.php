<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Deploy\Test\Unit\Model\Plugin;

use Magento\Deploy\Model\Plugin\ConfigChangeDetector;
use Magento\Deploy\Model\DeploymentConfig\ChangeDetector;
use Magento\Framework\App\FrontControllerInterface;
use Magento\Framework\App\RequestInterface;

class ConfigChangeDetectorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ConfigChangeDetector
     */
    private $configChangeDetectorPlugin;

    /**
     * @var ChangeDetector|\PHPUnit\Framework\MockObject\MockObject
     */
    private $changeDetectorMock;

    /**
     * @var FrontControllerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $frontControllerMock;

    /**
     * @var RequestInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $requestMock;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->changeDetectorMock = $this->getMockBuilder(ChangeDetector::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->frontControllerMock = $this->getMockBuilder(FrontControllerInterface::class)
            ->getMockForAbstractClass();
        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->getMockForAbstractClass();

        $this->configChangeDetectorPlugin = new ConfigChangeDetector($this->changeDetectorMock);
    }

    /**
     * @return void
     */
    public function testBeforeDispatchWithoutException()
    {
        $this->changeDetectorMock->expects($this->once())
            ->method('hasChanges')
            ->willReturn(false);
        $this->configChangeDetectorPlugin->beforeDispatch($this->frontControllerMock, $this->requestMock);
    }

    /**
     * @return void
     * @codingStandardsIgnoreStart
     * @codingStandardsIgnoreEnd
     */
    public function testBeforeDispatchWithException()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage('The configuration file has changed. Run the "app:config:import" or the "setup:upgrade" command to synchronize the configuration.');

        $this->changeDetectorMock->expects($this->once())
            ->method('hasChanges')
            ->willReturn(true);
        $this->configChangeDetectorPlugin->beforeDispatch($this->frontControllerMock, $this->requestMock);
    }
}
