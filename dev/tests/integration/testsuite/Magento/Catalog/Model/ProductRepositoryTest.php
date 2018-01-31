<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Provide tests for ProductRepository model.
 */
class ProductRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test subject.
     *
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ProductResource
     */
    private $productResource;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->productRepository = Bootstrap::getObjectManager()
            ->get(ProductRepositoryInterface::class);
        $this->productResource = Bootstrap::getObjectManager()
            ->get(ProductResource::class);
    }

    /**
     * @return Product
     */
    private function createProduct(): Product
    {
        return Bootstrap::getObjectManager()->create(Product::class);
    }

    /**
     * Test Product Repository can change(update) "sku" for given product.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoAppArea adminhtml
     */
    public function testUpdateProductSku()
    {
        $newSku = 'simple-edited';
        $productId = $this->productResource->getIdBySku('simple');
        $initialProduct = $this->createProduct()->load($productId);

        $initialProduct->setSku($newSku);
        $this->productRepository->save($initialProduct);

        $updatedProduct = $this->createProduct();
        $updatedProduct->load($productId);
        self::assertSame($newSku, $updatedProduct->getSku());
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/products_with_multiselect_attribute.php
     * @dataProvider provideAttributesForUpdate
     * @param string $code
     * @param mixed $newValue
     * @param mixed $expected
     * @return void
     * @throws
     */
    public function testUpdateAttributes(string $code, $newValue, $expected)
    {
        $productId = $this->productResource->getIdBySku('simple_ms_1');
        $product = $this->createProduct()->load($productId);
        $product->setData($code, $newValue);

        $this->productRepository->save($product);

        $product = $this->createProduct()->load($productId);
        $this->assertEquals($expected, $product->getData($code));
    }

    /**
     * @return array
     */
    public function provideAttributesForUpdate(): array
    {
        return [
            ['description', 'New description', 'New description'],
            [
                'multiselect_attribute',
                ['option_1', 'option_4'],
                'option_1,option_4'
            ]
        ];
    }

    /**
     * Check Product Repository able to correctly create product without specified type.
     *
     * @magentoDbIsolation enabled
     */
    public function testCreateWithoutSpecifiedType()
    {
        /** @var Product $product */
        $product = Bootstrap::getObjectManager()->get(ProductFactory::class)->create();
        $product->setName('Simple without specified type');
        $product->setSku('simple_without_specified_type');
        $product->setPrice(1.12);
        $product->setWeight(1.23);
        $product->setAttributeSetId(4);
        $product = $this->productRepository->save($product);

        self::assertSame('1.1200', $product->getPrice());
        self::assertSame('1.2300', $product->getWeight());
        self::assertSame('simple', $product->getTypeId());
    }
}
