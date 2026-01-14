<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block\Product\ProductList;

use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Catalog\Block\Product\ProductList\Related;
use Magento\Catalog\Model\Product;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class RelatedTest extends TestCase
{
    /**
     * @var Related
     */
    protected $block;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->block = $objectManager->getObject(Related::class);
    }

    protected function tearDown(): void
    {
        $this->block = null;
    }

    public function testGetIdentities()
    {
        $productTag = ['compare_item_1'];
        $product = $this->createMock(Product::class);
        $product->expects($this->once())->method('getIdentities')->willReturn($productTag);

        $itemsCollection = new \ReflectionProperty(
            Related::class,
            '_itemCollection'
        );
        $itemsCollection->setAccessible(true);
        $itemsCollection->setValue($this->block, [$product]);

        $this->assertEquals(
            $productTag,
            $this->block->getIdentities()
        );
    }

    /**
     * @param bool $isComposite
     * @param bool $isSaleable
     * @param bool $hasRequiredOptions
     * @param bool $canItemsAddToCart
     */
    #[DataProvider('canItemsAddToCartDataProvider')]
    public function testCanItemsAddToCart($isComposite, $isSaleable, $hasRequiredOptions, $canItemsAddToCart)
    {
        $product = $this->createPartialMock(Product::class, ['isComposite', 'isSaleable']);
        $product->method('isComposite')->willReturn($isComposite);
        $product->method('isSaleable')->willReturn($isSaleable);
        $product->setData('required_options', $hasRequiredOptions);

        $itemsCollection = new \ReflectionProperty(
            Related::class,
            '_itemCollection'
        );
        $itemsCollection->setAccessible(true);
        $itemsCollection->setValue($this->block, [$product]);

        $this->assertEquals(
            $canItemsAddToCart,
            $this->block->canItemsAddToCart()
        );
    }

    /**
     * @return array
     */
    public static function canItemsAddToCartDataProvider()
    {
        return [
            [false, true, false, true],
            [false, false, false, false],
            [true, false, false, false],
            [true, false, true, false],
        ];
    }
}
