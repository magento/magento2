<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleOptimizer\Test\Unit\Block\Code;

class ProductTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\GoogleOptimizer\Block\Code\Product
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
            \Magento\GoogleOptimizer\Block\Code\Product::class,
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
        $product = $this->createMock(\Magento\Catalog\Model\Product::class);
        $product->expects($this->once())->method('getIdentities')->willReturn($productTags);
        $this->registry->expects(
            $this->once()
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
