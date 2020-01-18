<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Block\Product\ProductList;

use Magento\Catalog\Block\Product\ProductList\Upsell as UpsellBlock;
use Magento\Catalog\Model\Product;

class UpsellTest extends \PHPUnit\Framework\TestCase
{
    const STUB_EMPTY_ARRAY = [];
    /**
     * @var UpsellBlock
     */
    protected $block;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->block = $objectManager->getObject(UpsellBlock::class);
    }

    protected function tearDown()
    {
        $this->block = null;
    }

    public function testGetIdentities()
    {
        $productTag = ['compare_item_1'];
        $product = $this->createMock(Product::class);
        $product->expects($this->once())->method('getIdentities')->will($this->returnValue($productTag));

        $itemsCollection = new \ReflectionProperty(UpsellBlock::class, '_items');
        $itemsCollection->setAccessible(true);
        $itemsCollection->setValue($this->block, [$product]);

        $this->assertEquals(
            $productTag,
            $this->block->getIdentities()
        );
    }

    public function testGetIdentitiesWhenItemGetIdentitiesReturnEmptyArray()
    {
        $product = $this->createMock(Product::class);
        $product->expects($this->once())->method('getIdentities')
            ->willReturn(self::STUB_EMPTY_ARRAY);

        $itemsCollection = new \ReflectionProperty(UpsellBlock::class, '_items');
        $itemsCollection->setAccessible(true);
        $itemsCollection->setValue($this->block, [$product]);

        $this->assertEquals(
            self::STUB_EMPTY_ARRAY,
            $this->block->getIdentities()
        );
    }

    public function testGetIdentitiesWhenGetItemsReturnEmptyArray()
    {
        $itemsCollection = new \ReflectionProperty(UpsellBlock::class, '_items');
        $itemsCollection->setAccessible(true);
        $itemsCollection->setValue($this->block, self::STUB_EMPTY_ARRAY);

        $this->assertEquals(
            self::STUB_EMPTY_ARRAY,
            $this->block->getIdentities()
        );
    }
}
