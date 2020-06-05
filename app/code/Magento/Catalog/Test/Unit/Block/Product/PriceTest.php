<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block\Product;

use Magento\Catalog\Block\Product\Price;
use Magento\Catalog\Model\Product;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class PriceTest extends TestCase
{
    /**
     * @var Price
     */
    protected $block;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->block = $objectManager->getObject(Price::class);
    }

    protected function tearDown(): void
    {
        $this->block = null;
    }

    public function testGetIdentities()
    {
        $productTags = ['catalog_product_1'];
        $product = $this->createMock(Product::class);
        $product->expects($this->once())->method('getIdentities')->willReturn($productTags);
        $this->block->setProduct($product);
        $this->assertEquals($productTags, $this->block->getIdentities());
    }
}
