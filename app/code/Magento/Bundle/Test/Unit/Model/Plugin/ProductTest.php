<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Model\Plugin;

use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Model\Product;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
    /** @var  \Magento\Bundle\Model\Plugin\Product */
    private $plugin;

    /** @var  MockObject|Type */
    private $type;

    /** @var  MockObject|Product */
    private $product;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEntityId'])
            ->getMock();
        $this->type = $this->getMockBuilder(Type::class)
            ->disableOriginalConstructor()
            ->setMethods(['getParentIdsByChild'])
            ->getMock();

        $this->plugin = $objectManager->getObject(
            \Magento\Bundle\Model\Plugin\Product::class,
            [
                'type' => $this->type,
            ]
        );
    }

    public function testAfterGetIdentities()
    {
        $baseIdentities = [
            'SomeCacheId',
            'AnotherCacheId',
        ];
        $id = 12345;
        $parentIds = [1, 2, 5, 100500];
        $expectedIdentities = [
            'SomeCacheId',
            'AnotherCacheId',
            Product::CACHE_TAG . '_' . 1,
            Product::CACHE_TAG . '_' . 2,
            Product::CACHE_TAG . '_' . 5,
            Product::CACHE_TAG . '_' . 100500,
        ];
        $this->product->expects($this->once())
            ->method('getEntityId')
            ->willReturn($id);
        $this->type->expects($this->once())
            ->method('getParentIdsByChild')
            ->with($id)
            ->willReturn($parentIds);
        $identities = $this->plugin->afterGetIdentities($this->product, $baseIdentities);
        $this->assertEquals($expectedIdentities, $identities);
    }
}
