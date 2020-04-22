<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProduct\Test\Unit\Block\Stockqty\Type;

use Magento\Catalog\Model\Product;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\GroupedProduct\Block\Stockqty\Type\Grouped;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GroupedTest extends TestCase
{
    /**
     * @var Grouped
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
            Grouped::class,
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
        $childProduct = $this->createMock(Product::class);
        $childProduct->expects($this->once())->method('getIdentities')->will($this->returnValue($productTags));
        $typeInstance = $this->createMock(\Magento\GroupedProduct\Model\Product\Type\Grouped::class);
        $typeInstance->expects(
            $this->once()
        )->method(
            'getAssociatedProducts'
        )->will(
            $this->returnValue([$childProduct])
        );
        $product = $this->createMock(Product::class);
        $product->expects($this->once())->method('getTypeInstance')->will($this->returnValue($typeInstance));
        $this->registry->expects(
            $this->any()
        )->method(
            'registry'
        )->with(
            'current_product'
        )->will(
            $this->returnValue($product)
        );
        $this->assertEquals($productTags, $this->block->getIdentities());
    }
}
