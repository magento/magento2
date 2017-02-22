<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Model\Template;

class FilterProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_filterMock;

    protected function setUp()
    {
        $this->_filterMock = $this->getMock('Magento\Cms\Model\Template\Filter', [], [], '', false);
        $this->_objectManagerMock = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $this->_objectManagerMock->expects($this->any())->method('get')->will($this->returnValue($this->_filterMock));
        $this->_model = new \Magento\Cms\Model\Template\FilterProvider($this->_objectManagerMock);
    }

    /**
     * @covers \Magento\Cms\Model\Template\FilterProvider::getBlockFilter
     */
    public function testGetBlockFilter()
    {
        $this->assertInstanceOf('Magento\Cms\Model\Template\Filter', $this->_model->getBlockFilter());
    }

    /**
     * @covers \Magento\Cms\Model\Template\FilterProvider::getPageFilter
     */
    public function testGetPageFilter()
    {
        $this->assertInstanceOf('Magento\Cms\Model\Template\Filter', $this->_model->getPageFilter());
    }

    /**
     * @covers \Magento\Cms\Model\Template\FilterProvider::getPageFilter
     */
    public function testGetPageFilterInnerCache()
    {
        $this->_objectManagerMock->expects($this->once())->method('get')->will($this->returnValue($this->_filterMock));
        $this->_model->getPageFilter();
        $this->_model->getPageFilter();
    }

    /**
     * @covers \Magento\Cms\Model\Template\FilterProvider::getPageFilter
     * @expectedException \Exception
     */
    public function testGetPageWrongInstance()
    {
        $someClassMock = $this->getMock('SomeClass');
        $objectManagerMock = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $objectManagerMock->expects($this->once())->method('get')->will($this->returnValue($someClassMock));
        $model = new \Magento\Cms\Model\Template\FilterProvider($objectManagerMock, 'SomeClass', 'SomeClass');
        $model->getPageFilter();
    }
}
