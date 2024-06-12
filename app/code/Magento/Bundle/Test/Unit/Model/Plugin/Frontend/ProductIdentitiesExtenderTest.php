<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Model\Plugin\Frontend;

use Magento\Bundle\Model\Plugin\Frontend\ProductIdentitiesExtender as ProductPlugin;
use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Model\Product;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for \Magento\Bundle\Model\Plugin\Frontend\ProductIdentitiesExtender
 */
class ProductIdentitiesExtenderTest extends TestCase
{
    /** @var ProductPlugin */
    private $plugin;

    /** @var MockObject|Type */
    private $type;

    /** @var MockObject|Product */
    private $product;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getEntityId', 'getTypeId'])
            ->getMock();

        $this->type = $this->getMockBuilder(Type::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getChildrenIds'])
            ->getMock();

        $this->plugin = new ProductPlugin($this->type);
    }

    public function testAfterGetIdentities(): void
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
        $this->product->expects($this->exactly(2))
            ->method('getEntityId')
            ->willReturn($id);
        $this->product->expects($this->exactly(2))
            ->method('getTypeId')
            ->willReturn(Type::TYPE_CODE);
        $this->type->expects($this->once())
            ->method('getChildrenIds')
            ->with($id)
            ->willReturn($childIds);
        $identities = $this->plugin->afterGetIdentities($this->product, $baseIdentities);
        $this->assertEquals($expectedIdentities, $identities);

        $this->type->expects($this->never())
            ->method('getChildrenIds')
            ->with($id)
            ->willReturn($childIds);
        $identities = $this->plugin->afterGetIdentities($this->product, $baseIdentities);
        $this->assertEquals($expectedIdentities, $identities);
    }
}
