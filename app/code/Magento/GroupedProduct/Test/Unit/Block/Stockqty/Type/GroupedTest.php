<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProduct\Test\Unit\Block\Stockqty\Type;

class GroupedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\GroupedProduct\Block\Stockqty\Type\Grouped
     */
    protected $block;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->registry = $this->getMock('Magento\Framework\Registry', [], [], '', false);
        $this->block = $objectManager->getObject(
            'Magento\GroupedProduct\Block\Stockqty\Type\Grouped',
            ['registry' => $this->registry]
        );
    }

    protected function tearDown()
    {
        $this->block = null;
    }

    public function testGetIdentities()
    {
        $productTags = ['catalog_product_1'];
        $childProduct = $this->getMock('Magento\Catalog\Model\Product', [], [], '', false);
        $childProduct->expects($this->once())->method('getIdentities')->will($this->returnValue($productTags));
        $typeInstance = $this->getMock(
            'Magento\GroupedProduct\Model\Product\Type\Grouped',
            [],
            [],
            '',
            false
        );
        $typeInstance->expects(
            $this->once()
        )->method(
            'getAssociatedProducts'
        )->will(
            $this->returnValue([$childProduct])
        );
        $product = $this->getMock('Magento\Catalog\Model\Product', [], [], '', false);
        $product->expects($this->once())->method('getTypeInstance')->will($this->returnValue($typeInstance));
        $this->registry->expects(
            $this->any()
        )->method(
            'registry'
        )->with(
            'current_product'
        )->will(
            $this->returnValue($product)
        );
        $this->assertEquals($productTags, $this->block->getIdentities());
    }
}
