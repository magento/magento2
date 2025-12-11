<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GroupedProduct\Test\Unit\Block\Product\Grouped;

use PHPUnit\Framework\Attributes\CoversClass;
use Magento\Backend\Block\Template\Context;
use Magento\GroupedProduct\Block\Product\Grouped\AssociatedProducts;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(\Magento\GroupedProduct\Block\Product\Grouped\AssociatedProducts::class)]
class AssociatedProductsTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $contextMock;

    /**
     * @var AssociatedProducts
     */
    protected $block;

    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(Context::class);
        $this->block = new AssociatedProducts($this->contextMock);
    }

    /**
     * @covers \Magento\GroupedProduct\Block\Product\Grouped\AssociatedProducts::getParentTab
     */
    public function testGetParentTab()
    {
        $this->assertEquals('product-details', $this->block->getParentTab());
    }

    /**
     * @covers \Magento\GroupedProduct\Block\Product\Grouped\AssociatedProducts::getTabLabel
     */
    public function testGetTabLabel()
    {
        $this->assertEquals('Grouped Products', $this->block->getTabLabel());
    }
}
