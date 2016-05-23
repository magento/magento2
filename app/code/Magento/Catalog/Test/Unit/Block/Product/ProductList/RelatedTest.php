<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Block\Product\ProductList;

class RelatedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Block\Product\ProductList\Related
     */
    protected $block;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
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

    /**
     * @dataProvider canItemsAddToCartDataProvider
     * @param bool $isComposite
     * @param bool $isSaleable
     * @param bool $hasRequiredOptions
     * @param bool $canItemsAddToCart
     */
    public function testCanItemsAddToCart($isComposite, $isSaleable, $hasRequiredOptions, $canItemsAddToCart)
    {
        $product = $this->getMock(
            'Magento\Catalog\Model\Product',
            ['isComposite', 'isSaleable', 'getRequiredOptions'],
            [],
            '',
            false
        );
        $product->expects($this->any())->method('isComposite')->willReturn($isComposite);
        $product->expects($this->any())->method('isSaleable')->willReturn($isSaleable);
        $product->expects($this->any())->method('getRequiredOptions')->willReturn($hasRequiredOptions);

        $itemsCollection = new \ReflectionProperty(
            'Magento\Catalog\Block\Product\ProductList\Related',
            '_itemCollection'
        );
        $itemsCollection->setAccessible(true);
        $itemsCollection->setValue($this->block, [$product]);

        $this->assertEquals(
            $canItemsAddToCart,
            $this->block->canItemsAddToCart()
        );
    }

    public function canItemsAddToCartDataProvider()
    {
        return [
            [false, true, false, true],
            [false, false, false, false],
            [true, false, false, false],
            [true, false, true, false],
        ];
    }
}
