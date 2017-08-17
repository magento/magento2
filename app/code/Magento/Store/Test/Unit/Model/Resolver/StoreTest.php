<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Test\Unit\Model\Resolver;

use \Magento\Store\Model\Resolver\Store;

/**
 * Test class for \Magento\Store\Model\Resolver\Store
 */
class StoreTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Store
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storeManagerMock;

    protected function setUp()
    {
        $this->_storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);

        $this->_model = new Store($this->_storeManagerMock);
    }

    protected function tearDown()
    {
        unset($this->_storeManagerMock);
    }

    public function testGetScope()
    {
        $scopeMock = $this->createMock(\Magento\Framework\App\ScopeInterface::class);
        $this->_storeManagerMock
            ->expects($this->once())
            ->method('getStore')
            ->with(0)
            ->will($this->returnValue($scopeMock));

        $this->assertEquals($scopeMock, $this->_model->getScope());
    }

    /**
     * @expectedException \Magento\Framework\Exception\State\InitException
     */
    public function testGetScopeWithInvalidScope()
    {
        $scopeMock = new \StdClass();
        $this->_storeManagerMock
            ->expects($this->once())
            ->method('getStore')
            ->with(0)
            ->will($this->returnValue($scopeMock));

        $this->assertEquals($scopeMock, $this->_model->getScope());
    }
}
