<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProduct\Test\Unit\Block\Adminhtml\Order\Create;

use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject;
use Magento\GroupedProduct\Block\Adminhtml\Order\Create\Sidebar;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\Sales\Block\Adminhtml\Order\Create\Sidebar\AbstractSidebar;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SidebarTest extends TestCase
{
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
        $this->itemMock = $this->createPartialMock(DataObject::class, ['getProduct']);
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
        $this->itemMock->expects($this->once())->method('getProduct')->will($this->returnValue($this->productMock));
        $this->productMock->expects(
            $this->once()
        )->method(
            'getTypeId'
        )->will(
            $this->returnValue(Grouped::TYPE_CODE)
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
