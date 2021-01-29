<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Model\Template;

class FilterProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    protected $_model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_objectManagerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_filterMock;

    protected function setUp(): void
    {
        $this->_filterMock = $this->createMock(\Magento\Cms\Model\Template\Filter::class);
        $this->_objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->_objectManagerMock->expects($this->any())->method('get')->willReturn($this->_filterMock);
        $this->_model = new \Magento\Cms\Model\Template\FilterProvider($this->_objectManagerMock);
    }

    /**
     * @covers \Magento\Cms\Model\Template\FilterProvider::getBlockFilter
     */
    public function testGetBlockFilter()
    {
        $this->assertInstanceOf(\Magento\Cms\Model\Template\Filter::class, $this->_model->getBlockFilter());
    }

    /**
     * @covers \Magento\Cms\Model\Template\FilterProvider::getPageFilter
     */
    public function testGetPageFilter()
    {
        $this->assertInstanceOf(\Magento\Cms\Model\Template\Filter::class, $this->_model->getPageFilter());
    }

    /**
     * @covers \Magento\Cms\Model\Template\FilterProvider::getPageFilter
     */
    public function testGetPageFilterInnerCache()
    {
        $this->_objectManagerMock->expects($this->once())->method('get')->willReturn($this->_filterMock);
        $this->_model->getPageFilter();
        $this->_model->getPageFilter();
    }

    /**
     * @covers \Magento\Cms\Model\Template\FilterProvider::getPageFilter
     */
    public function testGetPageWrongInstance()
    {
        $this->expectException(\Exception::class);

        $someClassMock = $this->createMock('SomeClass');
        $objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $objectManagerMock->expects($this->once())->method('get')->willReturn($someClassMock);
        $model = new \Magento\Cms\Model\Template\FilterProvider($objectManagerMock, 'SomeClass', 'SomeClass');
        $model->getPageFilter();
    }
}
