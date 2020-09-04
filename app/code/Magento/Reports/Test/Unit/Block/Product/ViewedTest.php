<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Reports\Test\Unit\Block\Product;

use Magento\Catalog\Model\Product;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Reports\Block\Product\Viewed;
use PHPUnit\Framework\TestCase;

class ViewedTest extends TestCase
{
    /**
     * @var Viewed
     */
    protected $block;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->block = $objectManager->getObject(Viewed::class);
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

        $collection = new \ReflectionProperty(Viewed::class, '_collection');
        $collection->setAccessible(true);
        $collection->setValue($this->block, [$product]);

        $this->assertEquals($productTags, $this->block->getIdentities());
    }
}
