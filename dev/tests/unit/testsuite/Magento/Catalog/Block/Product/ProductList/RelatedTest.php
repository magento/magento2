<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Product\ProductList;

class RelatedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Block\Product\ProductList\Related
     */
    protected $block;

    protected function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->block = $objectManager->getObject('Magento\Catalog\Block\Product\ProductList\Related');
    }

    protected function tearDown()
    {
        $this->block = null;
    }

    public function testGetIdentities()
    {
        $productTag = ['compare_item_1'];
        $product = $this->getMock('Magento\Catalog\Model\Product', [], [], '', false);
        $product->expects($this->once())->method('getIdentities')->will($this->returnValue($productTag));

        $itemsCollection = new \ReflectionProperty(
            'Magento\Catalog\Block\Product\ProductList\Related',
            '_itemCollection'
        );
        $itemsCollection->setAccessible(true);
        $itemsCollection->setValue($this->block, [$product]);

        $this->assertEquals(
            $productTag,
            $this->block->getIdentities()
        );
    }
}
