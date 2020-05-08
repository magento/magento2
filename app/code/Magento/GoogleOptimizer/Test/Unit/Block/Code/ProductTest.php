<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleOptimizer\Test\Unit\Block\Code;

use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\GoogleOptimizer\Block\Code\Product;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
    /**
     * @var Product
     */
    protected $block;

    /**
     * @var Registry|MockObject
     */
    protected $registry;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->registry = $this->createMock(Registry::class);
        $this->block = $objectManager->getObject(
            Product::class,
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
