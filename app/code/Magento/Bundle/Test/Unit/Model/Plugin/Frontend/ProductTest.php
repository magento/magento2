<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Test\Unit\Model\Plugin\Frontend;

use Magento\Bundle\Model\Plugin\Frontend\Product as ProductPlugin;
use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Model\Product;
use PHPUnit\Framework\MockObject\MockObject as MockObject;

class ProductTest extends \PHPUnit\Framework\TestCase
{
    /** @var  \Magento\Bundle\Model\Plugin\Product */
    private $plugin;

    /** @var  MockObject|Type */
    private $type;

    /** @var  MockObject|\Magento\Catalog\Model\Product */
    private $product;

    protected function setUp(): void
    {
        $this->product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEntityId'])
            ->getMock();

        $this->type = $this->getMockBuilder(Type::class)
            ->disableOriginalConstructor()
            ->setMethods(['getChildrenIds'])
            ->getMock();

        $this->plugin = new ProductPlugin($this->type);
    }

    public function testAfterGetIdentities()
    {
        $baseIdentities = [
            'SomeCacheId',
            'AnotherCacheId',
        ];
        $id = 12345;
        $childIds = [
            1 => [1, 2, 5, 100500],
            12 => [7, 22, 45, 24612]
        ];
        $expectedIdentities = [
            'SomeCacheId',
            'AnotherCacheId',
            Product::CACHE_TAG . '_' . 1,
            Product::CACHE_TAG . '_' . 2,
            Product::CACHE_TAG . '_' . 5,
            Product::CACHE_TAG . '_' . 100500,
            Product::CACHE_TAG . '_' . 7,
            Product::CACHE_TAG . '_' . 22,
            Product::CACHE_TAG . '_' . 45,
            Product::CACHE_TAG . '_' . 24612,
        ];
        $this->product->expects($this->once())
            ->method('getEntityId')
            ->willReturn($id);
        $this->type->expects($this->once())
            ->method('getChildrenIds')
            ->with($id)
            ->willReturn($childIds);
        $identities = $this->plugin->afterGetIdentities($this->product, $baseIdentities);
        $this->assertEquals($expectedIdentities, $identities);
    }
}
