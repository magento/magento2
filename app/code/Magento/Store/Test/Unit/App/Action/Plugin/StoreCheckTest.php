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
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storeMock;

    /**
     * @var \Magento\Framework\App\Action\AbstractAction|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectMock;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    protected function setUp()
    {
        $this->_storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->_storeMock = $this->createMock(\Magento\Store\Model\Store::class);
        $this->_storeManagerMock->expects(
            $this->any()
        )->method(
            'getStore'
        )->will(
            $this->returnValue($this->_storeMock)
        );
        $this->requestMock = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $this->subjectMock = $this->getMockBuilder(\Magento\Framework\App\Action\AbstractAction::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->_plugin = new \Magento\Store\App\Action\Plugin\StoreCheck($this->_storeManagerMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\State\InitException
     * @expectedExceptionMessage Current store is not active.
     */
    public function testBeforeDispatchWhenStoreNotActive()
    {
        $this->_storeMock->expects($this->any())->method('isActive')->will($this->returnValue(false));
        $this->_plugin->beforeDispatch($this->subjectMock, $this->requestMock);
    }

    public function testBeforeDispatchWhenStoreIsActive()
    {
        $this->_storeMock->expects($this->any())->method('isActive')->will($this->returnValue(true));
        $result = $this->_plugin->beforeDispatch($this->subjectMock, $this->requestMock);
        $this->assertNull($result);
    }
}
