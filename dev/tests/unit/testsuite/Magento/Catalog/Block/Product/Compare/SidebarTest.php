<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Product\Compare;

class SidebarTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Block\Product\Compare\Sidebar
     */
    protected $block;

    protected function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->block = $objectManager->getObject('Magento\Catalog\Block\Product\Compare\Sidebar');
    }

    protected function tearDown()
    {
        $this->block = null;
    }

    public function testGetIdentities()
    {
        $productTag = ['catalog_product_1'];
        $itemTag = ['compare_item_1'];
        $itemId = 1;

        $product = $this->getMock('Magento\Catalog\Model\Product', [], [], '', false);
        $product->expects($this->once())->method('getIdentities')->will($this->returnValue($productTag));
        $item = $this->getMock(
            'Magento\Catalog\Model\Product\Compare\Item',
            ['getProduct', '__wakeup'],
            [],
            '',
            false
        );
        $item->expects($this->once())->method('getProduct')->will($this->returnValue($product));
        $this->block->setItems([$item]);
        $this->block->setCatalogCompareItemId($itemId);
        $this->assertEquals(
            array_merge($productTag, $itemTag),
            $this->block->getIdentities()
        );
    }
}
