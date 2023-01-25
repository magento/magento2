<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\EntityManager\HydratorInterface;
use Magento\Framework\EntityManager\HydratorPool;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test Product Hydrator
 */
class ProductHydratorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Bootstrap
     */
    private $objectManager;

    /**
     * @var HydratorPool
     */
    private $hydratorPool;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->hydratorPool = $this->objectManager->create(HydratorPool::class);
    }

    /**
     * Test that Hydrator correctly populates entity with data
     */
    public function testProductHydrator()
    {
        $addAttributes = [
            'sku' => 'product_updated',
            'name' => 'Product (Updated)',
            'type_id' => 'simple',
            'status' => 1,
        ];

        /** @var Product $product */
        $product = $this->objectManager->create(Product::class);
        $product->setId(42)
            ->setSku('product')
            ->setName('Product')
            ->setPrice(10)
            ->setQty(123);
        $product->lockAttribute('sku');
        $product->lockAttribute('type_id');
        $product->lockAttribute('price');

        /** @var HydratorInterface $hydrator */
        $hydrator = $this->hydratorPool->getHydrator(ProductInterface::class);
        $hydrator->hydrate($product, $addAttributes);

        $expected = [
            'entity_id' => 42,
            'sku' => 'product_updated',
            'name' => 'Product (Updated)',
            'type_id' => 'simple',
            'status' => 1,
            'price' => 10,
            'qty' => 123,
        ];
        $this->assertEquals($expected, $product->getData());
    }
}
