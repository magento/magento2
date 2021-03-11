<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProduct\Test\Unit\Block\Stockqty\Type;

class GroupedTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\GroupedProduct\Block\Stockqty\Type\Grouped
     */
    protected $block;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $registry;

    protected function setUp(): void
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->registry = $this->createMock(\Magento\Framework\Registry::class);
        $this->block = $objectManager->getObject(
            \Magento\GroupedProduct\Block\Stockqty\Type\Grouped::class,
            ['registry' => $this->registry]
        );
    }

    protected function tearDown(): void
    {
        $this->block = null;
    }

    public function testGetIdentities()
    {
        $productTags = ['catalog_product_1'];
        $childProduct = $this->createMock(\Magento\Catalog\Model\Product::class);
        $childProduct->expects($this->once())->method('getIdentities')->willReturn($productTags);
        $typeInstance = $this->createMock(\Magento\GroupedProduct\Model\Product\Type\Grouped::class);
        $typeInstance->expects(
            $this->once()
        )->method(
            'getAssociatedProducts'
        )->willReturn(
            [$childProduct]
        );
        $product = $this->createMock(\Magento\Catalog\Model\Product::class);
        $product->expects($this->once())->method('getTypeInstance')->willReturn($typeInstance);
        $this->registry->expects(
            $this->any()
        )->method(
            'registry'
        )->with(
            'current_product'
        )->willReturn(
            $product
        );
        $this->assertEquals($productTags, $this->block->getIdentities());
    }
}
