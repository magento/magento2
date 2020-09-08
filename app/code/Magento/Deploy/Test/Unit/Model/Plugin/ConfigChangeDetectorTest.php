<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Deploy\Test\Unit\Model\Plugin;

use Magento\Deploy\Model\DeploymentConfig\ChangeDetector;
use Magento\Deploy\Model\Plugin\ConfigChangeDetector;
use Magento\Framework\App\FrontControllerInterface;
use Magento\Framework\App\RequestInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigChangeDetectorTest extends TestCase
{
    /**
     * @var ConfigChangeDetector
     */
    private $configChangeDetectorPlugin;

    /**
     * @var ChangeDetector|MockObject
     */
    private $changeDetectorMock;

    /**
     * @var FrontControllerInterface|MockObject
     */
    private $frontControllerMock;

    /**
     * @var RequestInterface|MockObject
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
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage(
            'The configuration file has changed. Run the "app:config:import" '
                . 'or the "setup:upgrade" command to synchronize the configuration.'
        );
        $this->changeDetectorMock->expects($this->once())
            ->method('hasChanges')
            ->willReturn(true);
        $this->configChangeDetectorPlugin->beforeDispatch($this->frontControllerMock, $this->requestMock);
    }
}
