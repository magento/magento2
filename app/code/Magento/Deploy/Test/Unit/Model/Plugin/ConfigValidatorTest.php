<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Test\Unit\Model\Plugin;

use Magento\Deploy\Model\Plugin\ConfigValidator;
use Magento\Deploy\Model\DeploymentConfig\Validator;
use Magento\Framework\App\FrontController;
use Magento\Framework\App\RequestInterface;

class ConfigValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigValidator
     */
    private $configValidatorPlugin;

    /**
     * @var Validator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configValidatorMock;

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
        $this->configValidatorMock = $this->getMockBuilder(Validator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->frontControllerMock = $this->getMockBuilder(FrontController::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->getMockForAbstractClass();

        $this->configValidatorPlugin = new ConfigValidator($this->configValidatorMock);
    }

    /**
     * @return void
     */
    public function testBeforeDispatchWithoutException()
    {
        $this->configValidatorMock->expects($this->once())
            ->method('isValid')
            ->willReturn(true);
        $this->configValidatorPlugin->beforeDispatch($this->frontControllerMock, $this->requestMock);
    }

    /**
     * @return void
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @codingStandardsIgnoreStart
     * @expectedExceptionMessage A change in configuration has been detected. Run app:config:import or setup:upgrade command to synchronize configuration.
     * @codingStandardsIgnoreEnd
     */
    public function testBeforeDispatchWithException()
    {
        $this->configValidatorMock->expects($this->once())
            ->method('isValid')
            ->willReturn(false);
        $this->configValidatorPlugin->beforeDispatch($this->frontControllerMock, $this->requestMock);
    }
}
