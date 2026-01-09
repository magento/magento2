<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GroupedProduct\Test\Unit\Block\Adminhtml\Order\Create;

use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\GroupedProduct\Block\Adminhtml\Order\Create\Sidebar;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\Sales\Block\Adminhtml\Order\Create\Sidebar\AbstractSidebar;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SidebarTest extends TestCase
{
    use MockCreationTrait;
    /**
     * @var Sidebar
     */
    protected $sidebarMock;

    /**
     * @var MockObject
     */
    protected $itemMock;

    /**
     * @var MockObject
     */
    protected $productMock;

    /**
     * @var MockObject
     */
    protected $subjectMock;

    /**
     * @var \Closure
     */
    protected $closureMock;

    protected function setUp(): void
    {
        $this->itemMock = $this->createPartialMockWithReflection(
            DataObject::class,
            ['getProduct']
        );
        $this->productMock = $this->createMock(Product::class);
        $this->subjectMock = $this->createMock(
            AbstractSidebar::class
        );
        $this->closureMock = function () {
            return 'Expected';
        };
        $this->sidebarMock = new Sidebar();
    }

    public function testAroundGetItemQtyWhenProductGrouped()
    {
        $this->itemMock->method('getProduct')->willReturn($this->productMock);
        $this->productMock->expects(
            $this->once()
        )->method(
            'getTypeId'
        )->willReturn(
            Grouped::TYPE_CODE
        );
        $this->assertEquals(
            '',
            $this->sidebarMock->aroundGetItemQty($this->subjectMock, $this->closureMock, $this->itemMock)
        );
    }

    public function testAroundGetItemQtyWhenProductNotGrouped()
    {
        $this->itemMock->method('getProduct')->willReturn($this->productMock);
        $this->productMock->expects($this->once())->method('getTypeId')->willReturn('one');
        $this->sidebarMock->aroundGetItemQty($this->subjectMock, $this->closureMock, $this->itemMock);
    }

    public function testAroundIsConfigurationRequiredWhenProductGrouped()
    {
        $this->assertTrue(
            $this->sidebarMock->aroundIsConfigurationRequired(
                $this->subjectMock,
                $this->closureMock,
                Grouped::TYPE_CODE
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
