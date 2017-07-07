<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProduct\Test\Unit\Block\Adminhtml\Order\Create;

class SidebarTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\GroupedProduct\Block\Adminhtml\Order\Create\Sidebar
     */
    protected $sidebarMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $itemMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectMock;

    /**
     * @var \Closure
     */
    protected $closureMock;

    protected function setUp()
    {
        $this->itemMock = $this->getMock(\Magento\Framework\DataObject::class, ['getProduct'], [], '', false);
        $this->productMock = $this->getMock(\Magento\Catalog\Model\Product::class, [], [], '', false);
        $this->subjectMock = $this->getMock(
            \Magento\Sales\Block\Adminhtml\Order\Create\Sidebar\AbstractSidebar::class,
            [],
            [],
            '',
            false
        );
        $this->closureMock = function () {
            return 'Expected';
        };
        $this->sidebarMock = new \Magento\GroupedProduct\Block\Adminhtml\Order\Create\Sidebar();
    }

    public function testAroundGetItemQtyWhenProductGrouped()
    {
        $this->itemMock->expects($this->once())->method('getProduct')->will($this->returnValue($this->productMock));
        $this->productMock->expects(
            $this->once()
        )->method(
            'getTypeId'
        )->will(
            $this->returnValue(\Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE)
        );
        $this->assertEquals(
            '',
            $this->sidebarMock->aroundGetItemQty($this->subjectMock, $this->closureMock, $this->itemMock)
        );
    }

    public function testAroundGetItemQtyWhenProductNotGrouped()
    {
        $this->itemMock->expects($this->once())->method('getProduct')->will($this->returnValue($this->productMock));
        $this->productMock->expects($this->once())->method('getTypeId')->will($this->returnValue('one'));
        $this->sidebarMock->aroundGetItemQty($this->subjectMock, $this->closureMock, $this->itemMock);
    }

    public function testAroundIsConfigurationRequiredWhenProductGrouped()
    {
        $this->assertEquals(
            true,
            $this->sidebarMock->aroundIsConfigurationRequired(
                $this->subjectMock,
                $this->closureMock,
                \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE
            )
        );
    }

    public function testAroundIsConfigurationRequiredWhenProductNotGrouped()
    {
        $this->assertEquals(
            'Expected',
            $this->sidebarMock->aroundIsConfigurationRequired($this->subjectMock, $this->closureMock, 'someValue')
        );
    }
}
