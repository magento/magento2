<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\GroupedProduct\Block\Product\Grouped;

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
        $this->block = new AssociatedProducts($this->contextMock);
    }

    /**
     * @covers Magento\GroupedProduct\Block\Product\Grouped\AssociatedProducts::getParentTab
     */
    public function testGetParentTab()
    {
        $this->assertEquals('product-details', $this->block->getParentTab());
    }

    /**
     * @covers Magento\GroupedProduct\Block\Product\Grouped\AssociatedProducts::getTabLabel
     */
    public function testGetTabLabel()
    {
        $this->assertEquals('Grouped Products', $this->block->getTabLabel());
    }
}
