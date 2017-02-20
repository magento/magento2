<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\DeploymentConfig\Plugin;

use Magento\Framework\App\DeploymentConfig\Plugin\ConfigHashValidator;
use Magento\Framework\App\DeploymentConfig\ConfigHashManager;
use Magento\Framework\App\FrontController;
use Magento\Framework\App\RequestInterface;

class ConfigHashValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigHashValidator
     */
    private $configHashValidator;

    /**
     * @var ConfigHashManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configHashManagerMock;

    /**
     * @var FrontController|\PHPUnit_Framework_MockObject_MockObject
     */
    private $frontControllerMock;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->configHashManagerMock = $this->getMockBuilder(ConfigHashManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->frontControllerMock = $this->getMockBuilder(FrontController::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->getMockForAbstractClass();

        $this->configHashValidator = new ConfigHashValidator($this->configHashManagerMock);
    }

    /**
     * @return void
     */
    public function testBeforeDispatchWithoutException()
    {
        $this->configHashManagerMock->expects($this->once())
            ->method('isHashValid')
            ->willReturn(true);
        $this->configHashValidator->beforeDispatch($this->frontControllerMock, $this->requestMock);
    }

    /**
     * @return void
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @codingStandardsIgnoreStart
     * @expectedExceptionMessage A change in configuration has been detected. Run config:sync or setup:upgrade command to synchronize configuration.
     * @codingStandardsIgnoreEnd
     */
    public function testBeforeDispatchWithException()
    {
        $this->configHashManagerMock->expects($this->once())
            ->method('isHashValid')
            ->willReturn(false);
        $this->configHashValidator->beforeDispatch($this->frontControllerMock, $this->requestMock);
    }
}
