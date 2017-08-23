<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Developer\Test\Unit\Model\Logger\Handler;

use Magento\Developer\Model\Logger\Handler\Debug;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\ScopeInterface;
use Monolog\Formatter\FormatterInterface;
use Monolog\Logger;
use Magento\Framework\App\DeploymentConfig;

/**
 * Class DebugTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DebugTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Debug
     */
    private $model;

    /**
     * @var DriverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filesystemMock;

    /**
     * @var State|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stateMock;

    /**
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfigMock;

    /**
     * @var FormatterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $formatterMock;

    /**
     * @var DeploymentConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $deploymentConfigMock;

    protected function setUp()
    {
        $this->filesystemMock = $this->getMockBuilder(DriverInterface::class)
            ->getMockForAbstractClass();
        $this->stateMock = $this->getMockBuilder(State::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->getMockForAbstractClass();
        $this->formatterMock = $this->getMockBuilder(FormatterInterface::class)
            ->getMockForAbstractClass();
        $this->deploymentConfigMock = $this->getMockBuilder(DeploymentConfig::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->getMock();

        $this->formatterMock->expects($this->any())
            ->method('format')
            ->willReturn(null);

        $this->model = (new ObjectManager($this))->getObject(Debug::class, [
            'filesystem' => $this->filesystemMock,
            'state' => $this->stateMock,
            'scopeConfig' => $this->scopeConfigMock,
            'deploymentConfig' => $this->deploymentConfigMock
        ]);
        $this->model->setFormatter($this->formatterMock);
    }

    public function testHandle()
    {
        $this->deploymentConfigMock->expects($this->once())
            ->method('isAvailable')
            ->willReturn(true);
        $this->stateMock->expects($this->never())
            ->method('getMode');
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with('dev/debug/debug_logging', ScopeInterface::SCOPE_STORE, null)
            ->willReturn(true);

        $this->assertTrue($this->model->isHandling(['formatted' => false, 'level' => Logger::DEBUG]));
    }

    public function testHandleDisabledByProduction()
    {
        $this->deploymentConfigMock->expects($this->once())
            ->method('isAvailable')
            ->willReturn(true);
        $this->stateMock->expects($this->never())
            ->method('getMode');
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue');

        $this->assertFalse($this->model->isHandling(['formatted' => false, 'level' => Logger::DEBUG]));
    }

    public function testHandleDisabledByConfig()
    {
        $this->deploymentConfigMock->expects($this->once())
            ->method('isAvailable')
            ->willReturn(true);
        $this->stateMock->expects($this->never())
            ->method('getMode');
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with('dev/debug/debug_logging', ScopeInterface::SCOPE_STORE, null)
            ->willReturn(false);

        $this->assertFalse($this->model->isHandling(['formatted' => false, 'level' => Logger::DEBUG]));
    }

    public function testHandleDisabledByLevel()
    {
        $this->deploymentConfigMock->expects($this->once())
            ->method('isAvailable')
            ->willReturn(true);
        $this->stateMock->expects($this->never())
            ->method('getMode');
        $this->scopeConfigMock->expects($this->never())
            ->method('getValue');

        $this->assertFalse($this->model->isHandling(['formatted' => false, 'level' => Logger::API]));
    }

    public function testDeploymentConfigIsNotAvailable()
    {
        $this->deploymentConfigMock->expects($this->once())
            ->method('isAvailable')
            ->willReturn(false);
        $this->stateMock->expects($this->never())
            ->method('getMode');
        $this->scopeConfigMock->expects($this->never())
            ->method('getValue');

        $this->assertTrue($this->model->isHandling(['formatted' => false, 'level' => Logger::DEBUG]));
    }
}
