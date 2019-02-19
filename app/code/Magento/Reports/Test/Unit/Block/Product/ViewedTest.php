<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Test\Unit\Block\Product;

class ViewedTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Reports\Block\Product\Viewed
     */
    protected $block;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->block = $objectManager->getObject(\Magento\Reports\Block\Product\Viewed::class);
    }

    protected function tearDown()
    {
        $this->block = null;
    }

    public function testGetIdentities()
    {
        $productTags = ['catalog_product_1'];

        $product = $this->createMock(\Magento\Catalog\Model\Product::class);
        $product->expects($this->once())->method('getIdentities')->will($this->returnValue($productTags));

        $collection = new \ReflectionProperty(\Magento\Reports\Block\Product\Viewed::class, '_collection');
        $collection->setAccessible(true);
        $collection->setValue($this->block, [$product]);

        $this->assertEquals($productTags, $this->block->getIdentities());
    }
}
