<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Test\Unit\App\Action\Plugin;

class StoreCheckTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Store\App\Action\Plugin\StoreCheck
     */
    protected $_plugin;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_storeManagerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_storeMock;

    /**
     * @var \Magento\Framework\App\Action\AbstractAction|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $subjectMock;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $requestMock;

    protected function setUp(): void
    {
        $this->_storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->_storeMock = $this->createMock(\Magento\Store\Model\Store::class);
        $this->_storeManagerMock->expects(
            $this->any()
        )->method(
            'getStore'
        )->willReturn(
            $this->_storeMock
        );
        $this->requestMock = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $this->subjectMock = $this->getMockBuilder(\Magento\Framework\App\Action\AbstractAction::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->_plugin = new \Magento\Store\App\Action\Plugin\StoreCheck($this->_storeManagerMock);
    }

    /**
     */
    public function testBeforeDispatchWhenStoreNotActive()
    {
        $this->expectException(\Magento\Framework\Exception\State\InitException::class);
        $this->expectExceptionMessage('Current store is not active.');

        $this->_storeMock->expects($this->any())->method('isActive')->willReturn(false);
        $this->_plugin->beforeDispatch($this->subjectMock, $this->requestMock);
    }

    public function testBeforeDispatchWhenStoreIsActive()
    {
        $this->_storeMock->expects($this->any())->method('isActive')->willReturn(true);
        $result = $this->_plugin->beforeDispatch($this->subjectMock, $this->requestMock);
        $this->assertNull($result);
    }
}
