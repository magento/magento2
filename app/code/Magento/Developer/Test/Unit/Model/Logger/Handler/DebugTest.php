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
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

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
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var StoreInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeMock;

    protected function setUp()
    {
        $this->filesystemMock = $this->getMockBuilder(DriverInterface::class)
            ->getMockForAbstractClass();
        $this->stateMock = $this->getMockBuilder(State::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->getMockForAbstractClass();
        $this->storeMock = $this->getMockBuilder(StoreInterface::class)
            ->getMockForAbstractClass();
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->getMockForAbstractClass();

        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->willReturn($this->storeMock);

        $this->model = (new ObjectManager($this))->getObject(Debug::class, [
            'filesystem' => $this->filesystemMock,
            'state' => $this->stateMock,
            'scopeConfig' => $this->scopeConfigMock,
            'storeManager' => $this->storeManagerMock
        ]);
    }

    public function testWrite()
    {
        $this->storeMock->expects($this->once())
            ->method('getCode')
            ->willReturn('test_code');
        $this->stateMock->expects($this->once())
            ->method('getMode')
            ->willReturn(State::MODE_DEVELOPER);
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with('debug_logging', ScopeInterface::SCOPE_STORE, 'test_code')
            ->willReturn(true);

        $this->model->write(['formatted' => false]);
    }

    public function testWriteWithDisabledByProduction()
    {
        $this->storeMock->expects($this->once())
            ->method('getCode')
            ->willReturn('test_code');
        $this->stateMock->expects($this->once())
            ->method('getMode')
            ->willReturn(State::MODE_PRODUCTION);
        $this->scopeConfigMock->expects($this->never())
            ->method('getValue');

        $this->assertNull(
            $this->model->write(['formatted' => false])
        );
    }

    public function testWriteDisabledByConfig()
    {
        $this->storeMock->expects($this->once())
            ->method('getCode')
            ->willReturn('test_code');
        $this->stateMock->expects($this->once())
            ->method('getMode')
            ->willReturn(State::MODE_DEVELOPER);
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with('debug_logging', ScopeInterface::SCOPE_STORE, 'test_code')
            ->willReturn(false);

        $this->assertNull(
            $this->model->write(['formatted' => false])
        );
    }
}
