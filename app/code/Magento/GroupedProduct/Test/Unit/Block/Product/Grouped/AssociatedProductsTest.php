<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProduct\Test\Unit\Block\Product\Grouped;

class AssociatedProductsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\GroupedProduct\Block\Product\Grouped\AssociatedProducts
     */
    protected $block;

    protected function setUp()
    {
        $this->contextMock = $this->getMock('\Magento\Backend\Block\Template\Context', [], [], '', false);
        $this->block = new \Magento\GroupedProduct\Block\Product\Grouped\AssociatedProducts($this->contextMock);
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
