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
 *
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
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
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->productRepository = Bootstrap::getObjectManager()
            ->get(ProductRepositoryInterface::class);
        $this->productResource = Bootstrap::getObjectManager()
            ->get(ProductResource::class);
        $this->searchCriteriaBuilder = Bootstrap::getObjectManager()
            ->create(\Magento\Framework\Api\SearchCriteriaBuilder::class);
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

    /**
     * Tests product repository update should use provided store code.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testProductUpdate()
    {
        $sku = 'simple';
        $nameUpdated = 'updated';
        $product = $this->productRepository->get($sku, false, 0);
        $product->setName($nameUpdated);
        $this->productRepository->save($product);
        $product = $this->productRepository->get($sku, false, 0);
        self::assertEquals(
            $nameUpdated,
            $product->getName()
        );
    }

    /**
     * Check a case when product should be retrieved with different SKU variations.
     *
     * @param string $sku
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @dataProvider skuDataProvider
     */
    public function testGetProduct(string $sku)
    {
        $expectedSku = 'simple';
        $product = $this->productRepository->get($sku);

        self::assertNotEmpty($product);
        self::assertEquals($expectedSku, $product->getSku());
    }

    /**
     * Get list of SKU variations for the same product.
     *
     * @return array
     */
    public function skuDataProvider(): array
    {
        return [
            ['sku' => 'simple'],
            ['sku' => 'Simple'],
            ['sku' => 'simple ']
        ];
    }

    /**
     * Checks filtering by store_id.
     *
     * @magentoDataFixture Magento/Catalog/Model/ResourceModel/_files/product_simple.php
     * @return void
     */
    public function testFilterByStoreId()
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('store_id', '1', 'eq')
            ->create();
        $list = $this->productRepository->getList($searchCriteria);
        $count = $list->getTotalCount();

        $this->assertGreaterThanOrEqual(1, $count);
    }

    /**
     * Test save product with gallery image
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple_with_image.php
     *
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function testSaveProductWithGalleryImage()
    {
        /** @var $mediaConfig \Magento\Catalog\Model\Product\Media\Config */
        $mediaConfig = Bootstrap::getObjectManager()
            ->get(\Magento\Catalog\Model\Product\Media\Config::class);

        /** @var $mediaDirectory \Magento\Framework\Filesystem\Directory\WriteInterface */
        $mediaDirectory = Bootstrap::getObjectManager()
            ->get(\Magento\Framework\Filesystem::class)
            ->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);

        $product = Bootstrap::getObjectManager()->create(\Magento\Catalog\Model\Product::class);
        $product->load(1);

        $path = $mediaConfig->getBaseMediaPath() . '/magento_image.jpg';
        $absolutePath = $mediaDirectory->getAbsolutePath() . $path;
        $product->addImageToMediaGallery($absolutePath, [
            'image',
            'small_image',
        ], false, false);

        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
        $productRepository = Bootstrap::getObjectManager()
            ->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $productRepository->save($product);

        $gallery = $product->getData('media_gallery');
        $this->assertArrayHasKey('images', $gallery);
        $images = array_values($gallery['images']);

        $this->assertNotEmpty($gallery);
        $this->assertTrue(isset($images[0]['file']));
        $this->assertStringStartsWith('/m/a/magento_image', $images[0]['file']);
        $this->assertArrayHasKey('media_type', $images[0]);
        $this->assertEquals('image', $images[0]['media_type']);
        $this->assertStringStartsWith('/m/a/magento_image', $product->getData('image'));
        $this->assertStringStartsWith('/m/a/magento_image', $product->getData('small_image'));
    }
}
