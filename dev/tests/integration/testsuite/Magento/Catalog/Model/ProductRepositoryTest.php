<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Media\ConfigInterface;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Catalog\Model\ProductLayoutUpdateManager;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Provide tests for ProductRepository model.
 *
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductRepositoryTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Test subject.
     *
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var SearchCriteriaBuilder
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
     * @var ProductLayoutUpdateManager
     */
    private $layoutManager;

    /**
     * @var ConfigInterface
     */
    private $mediaConfig;

    /**
     * @var WriteInterface
     */
    private $mediaDirectory;

    /**
     * @var array
     */
    private $productSkusToDelete = [];

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->productRepository->cleanCache();
        $this->searchCriteriaBuilder = $this->objectManager->get(SearchCriteriaBuilder::class);
        $this->productFactory = $this->objectManager->get(ProductFactory::class);
        $this->productResource = $this->objectManager->get(ProductResource::class);
        $this->layoutManager = $this->objectManager->get(ProductLayoutUpdateManager::class);
        $this->mediaConfig = $this->objectManager->get(ConfigInterface::class);
        $this->mediaDirectory = $this->objectManager->get(Filesystem::class)
            ->getDirectoryWrite(DirectoryList::MEDIA);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        foreach ($this->productSkusToDelete as $productSku) {
            try {
                $this->productRepository->deleteById($productSku);
            } catch (NoSuchEntityException $e) {
                //Product already removed
            }
        }

        parent::tearDown();
    }

    /**
     * Checks filtering by store_id
     *
     * @magentoDataFixture Magento/Catalog/Model/ResourceModel/_files/product_simple.php
     * @return void
     */
    public function testFilterByStoreId(): void
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
    public function testGetProduct(string $sku): void
    {
        $expectedSku = 'simple';
        $product = $this->productRepository->get($sku);
        $this->assertEquals($expectedSku, $product->getSku());
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
     * @return void
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws StateException
     */
    public function testSaveProductWithGalleryImage(): void
    {
        $product = $this->productRepository->get('simple');
        $path = $this->mediaConfig->getBaseMediaPath() . '/magento_image.jpg';
        $absolutePath = $this->mediaDirectory->getAbsolutePath() . $path;
        $product->addImageToMediaGallery(
            $absolutePath,
            [
                'image',
                'small_image',
            ],
            false,
            false
        );
        $this->productRepository->save($product);
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
     * @magentoAppArea adminhtml
     * @return void
     */
    public function testUpdateProductSku(): void
    {
        $newSku = 'simple-edited';
        $productId = $this->productResource->getIdBySku('simple');
        $initialProduct = $this->productFactory->create();
        $this->productResource->load($initialProduct, $productId);
        $initialProduct->setSku($newSku);
        $this->productRepository->save($initialProduct);
        $this->productSkusToDelete[] = $newSku;
        $updatedProduct = $this->productFactory->create();
        $this->productResource->load($updatedProduct, $productId);
        $this->assertSame($newSku, $updatedProduct->getSku());
    }

    /**
     * Test that custom layout file attribute is saved.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @return void
     * @throws \Throwable
     */
    public function testCustomLayout(): void
    {
        $product = $this->productRepository->get('simple');
        $newFile = 'test';
        $this->layoutManager->setFakeFiles((int)$product->getId(), [$newFile]);
        $product->setCustomAttribute('custom_layout_update_file', $newFile);
        $this->productRepository->save($product);
        $product = $this->productRepository->get('simple');
        $this->assertEquals($newFile, $product->getCustomAttribute('custom_layout_update_file')->getValue());
        $newFile = 'does not exist';
        $product->setCustomAttribute('custom_layout_update_file', $newFile);
        $this->expectException(LocalizedException::class);
        $this->productRepository->save($product);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple_duplicated.php
     * @magentoAppArea adminhtml
     *
     * @return void
     */
    public function testDeleteByIdSimpleProduct(): void
    {
        $productSku = 'simple-1';
        $result = $this->productRepository->deleteById($productSku);
        $this->assertTrue($result);
        $this->assertProductNotExist($productSku);
    }

    /**
     * Assert that product does not exist.
     *
     * @param string $sku
     * @return void
     */
    private function assertProductNotExist(string $sku): void
    {
        $this->expectExceptionObject(new NoSuchEntityException(
            __("The product that was requested doesn't exist. Verify the product and try again.")
        ));
        $this->productRepository->get($sku);
    }
}
