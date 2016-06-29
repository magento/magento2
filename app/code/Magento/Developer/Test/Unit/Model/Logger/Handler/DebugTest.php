<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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

/**
 * Class DebugTest
 */
class DebugTest extends \PHPUnit_Framework_TestCase
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

        $this->formatterMock->expects($this->any())
            ->method('format')
            ->willReturn(null);

        $this->model = (new ObjectManager($this))->getObject(Debug::class, [
            'filesystem' => $this->filesystemMock,
            'state' => $this->stateMock,
            'scopeConfig' => $this->scopeConfigMock,
        ]);
        $this->model->setFormatter($this->formatterMock);
    }

    public function testHandle()
    {
        $this->stateMock->expects($this->once())
            ->method('getMode')
            ->willReturn(State::MODE_DEVELOPER);
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with('dev/debug/debug_logging', ScopeInterface::SCOPE_STORE, null)
            ->willReturn(true);

        $this->model->handle(['formatted' => false, 'level' => Logger::DEBUG]);
    }

    public function testHandleDisabledByProduction()
    {
        $this->stateMock->expects($this->once())
            ->method('getMode')
            ->willReturn(State::MODE_PRODUCTION);
        $this->scopeConfigMock->expects($this->never())
            ->method('getValue');

        $this->model->handle(['formatted' => false, 'level' => Logger::DEBUG]);
    }

    public function testHandleDisabledByConfig()
    {
        $this->stateMock->expects($this->once())
            ->method('getMode')
            ->willReturn(State::MODE_DEVELOPER);
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with('dev/debug/debug_logging', ScopeInterface::SCOPE_STORE, null)
            ->willReturn(false);

        $this->model->handle(['formatted' => false, 'level' => Logger::DEBUG]);
    }

    public function testHandleDisabledByLevel()
    {
        $this->stateMock->expects($this->never())
            ->method('getMode');
        $this->scopeConfigMock->expects($this->never())
            ->method('getValue');

        $this->model->handle(['formatted' => false, 'level' => Logger::API]);
    }
}
