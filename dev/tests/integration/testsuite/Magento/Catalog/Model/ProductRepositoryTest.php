<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Provide tests for ProductRepository model.
 *
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * @var ProductResource
     */
    private $productResource;

    /**
     * Sets up common objects
     */
    protected function setUp()
    {
        $this->productRepository = \Magento\Framework\App\ObjectManager::getInstance()->create(
            \Magento\Catalog\Api\ProductRepositoryInterface::class
        );

        $this->searchCriteriaBuilder = \Magento\Framework\App\ObjectManager::getInstance()->create(
            \Magento\Framework\Api\SearchCriteriaBuilder::class
        );

        $this->productFactory = Bootstrap::getObjectManager()->get(ProductFactory::class);

        $this->productResource = Bootstrap::getObjectManager()->get(ProductResource::class);

        $this->productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
    }

    /**
     * Checks filtering by store_id
     *
     * @magentoDataFixture Magento/Catalog/Model/ResourceModel/_files/product_simple.php
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
     * Check a case when product should be retrieved with different SKU variations.
     *
     * @param string $sku
     * @return void
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @dataProvider skuDataProvider
     */
    public function testGetProduct(string $sku) : void
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
            ['sku' => 'simple '],
        ];
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
    public function testSaveProductWithGalleryImage(): void
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
        $product->addImageToMediaGallery(
            $absolutePath,
            [
            'image',
            'small_image',
            ],
            false,
            false
        );

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

    /**
     * Test Product Repository can change(update) "sku" for given product.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDbIsolation enabled
     * @magentoAppArea adminhtml
     */
    public function testUpdateProductSku()
    {
        $newSku = 'simple-edited';
        $productId = $this->productResource->getIdBySku('simple');
        $initialProduct = $this->productFactory->create();
        $this->productResource->load($initialProduct, $productId);

        $initialProduct->setSku($newSku);
        $this->productRepository->save($initialProduct);

        $updatedProduct = $this->productFactory->create();
        $this->productResource->load($updatedProduct, $productId);
        self::assertSame($newSku, $updatedProduct->getSku());

        //clean up.
        $this->productRepository->delete($updatedProduct);
    }
}
