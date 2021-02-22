<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProduct\Test\Unit\Block\Adminhtml\Order\Create;

class SidebarTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\GroupedProduct\Block\Adminhtml\Order\Create\Sidebar
     */
    protected $sidebarMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $itemMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $productMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $subjectMock;

    /**
     * @var \Closure
     */
    protected $closureMock;

    protected function setUp(): void
    {
        $this->itemMock = $this->createPartialMock(\Magento\Framework\DataObject::class, ['getProduct']);
        $this->productMock = $this->createMock(\Magento\Catalog\Model\Product::class);
        $this->subjectMock = $this->createMock(
            \Magento\Sales\Block\Adminhtml\Order\Create\Sidebar\AbstractSidebar::class
        );
        $this->closureMock = function () {
            return 'Expected';
        };
        $this->sidebarMock = new \Magento\GroupedProduct\Block\Adminhtml\Order\Create\Sidebar();
    }

    public function testAroundGetItemQtyWhenProductGrouped()
    {
        $this->itemMock->expects($this->once())->method('getProduct')->willReturn($this->productMock);
        $this->productMock->expects(
            $this->once()
        )->method(
            'getTypeId'
        )->willReturn(
            \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE
        );
        $this->assertEquals(
            '',
            $this->sidebarMock->aroundGetItemQty($this->subjectMock, $this->closureMock, $this->itemMock)
        );
    }

    public function testAroundGetItemQtyWhenProductNotGrouped()
    {
        $this->itemMock->expects($this->once())->method('getProduct')->willReturn($this->productMock);
        $this->productMock->expects($this->once())->method('getTypeId')->willReturn('one');
        $this->sidebarMock->aroundGetItemQty($this->subjectMock, $this->closureMock, $this->itemMock);
    }

    public function testAroundIsConfigurationRequiredWhenProductGrouped()
    {
        $this->assertTrue(
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
