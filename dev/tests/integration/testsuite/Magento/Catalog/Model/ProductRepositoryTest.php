<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\TestFramework\Catalog\Model\ProductLayoutUpdateManager;
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
    private const STUB_STORE_ID = 1;

    private const STUB_STORE_ID_GLOBAL = 0;

    private const STUB_PRODUCT_NAME = 'Simple Product';

    private const STUB_UPDATED_PRODUCT_NAME = 'updated';

    private const STUB_PRODUCT_SKU = 'simple';

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
     * Sets up common objects
     */
    protected function setUp()
    {
        $this->productRepository = Bootstrap::getObjectManager()->create(ProductRepositoryInterface::class);
        $this->searchCriteriaBuilder = Bootstrap::getObjectManager()->get(SearchCriteriaBuilder::class);
        $this->productFactory = Bootstrap::getObjectManager()->get(ProductFactory::class);
        $this->productResource = Bootstrap::getObjectManager()->get(ProductResource::class);
        $this->layoutManager = Bootstrap::getObjectManager()->get(ProductLayoutUpdateManager::class);
    }

    /**
     * Create new subject instance.
     *
     * @return ProductRepositoryInterface
     */
    private function createRepo(): ProductRepositoryInterface
    {
        return Bootstrap::getObjectManager()->create(ProductRepositoryInterface::class);
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

    /**
     * Test that custom layout file attribute is saved.
     *
     * @return void
     * @throws \Throwable
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testCustomLayout(): void
    {
        //New valid value
        $repo = $this->createRepo();
        $product = $repo->get('simple');
        $newFile = 'test';
        $this->layoutManager->setFakeFiles((int)$product->getId(), [$newFile]);
        $product->setCustomAttribute('custom_layout_update_file', $newFile);
        $repo->save($product);
        $repo = $this->createRepo();
        $product = $repo->get('simple');
        $this->assertEquals($newFile, $product->getCustomAttribute('custom_layout_update_file')->getValue());

        //Setting non-existent value
        $newFile = 'does not exist';
        $product->setCustomAttribute('custom_layout_update_file', $newFile);
        $caughtException = false;
        try {
            $repo->save($product);
        } catch (LocalizedException $exception) {
            $caughtException = true;
        }
        $this->assertTrue($caughtException);
    }

    /**
     * Tests product repository update
     *
     * @dataProvider productUpdateDataProvider
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @param int $storeId
     * @param int $checkStoreId
     * @param string $expectedNameStore
     * @param string $expectedNameCheckedStore
     */
    public function testProductUpdate(
        int $storeId,
        int $checkStoreId,
        string $expectedNameStore,
        string $expectedNameCheckedStore
    ): void {
        $sku = self::STUB_PRODUCT_SKU;

        $product = $this->productRepository->get($sku, false, $storeId);
        $product->setName(self::STUB_UPDATED_PRODUCT_NAME);
        $this->productRepository->save($product);
        $productNameStoreId = $this->productRepository->get($sku, false, $storeId)->getName();
        $productNameCheckedStoreId = $this->productRepository->get($sku, false, $checkStoreId)->getName();

        $this->assertEquals($expectedNameStore, $productNameStoreId);
        $this->assertEquals($expectedNameCheckedStore, $productNameCheckedStoreId);
    }

    /**
     * Product update data provider
     *
     * @return array
     */
    public function productUpdateDataProvider(): array
    {
        return [
            'Updating for global store' => [
                self::STUB_STORE_ID_GLOBAL,
                self::STUB_STORE_ID,
                self::STUB_UPDATED_PRODUCT_NAME,
                self::STUB_UPDATED_PRODUCT_NAME,
            ],
            'Updating for store' => [
                self::STUB_STORE_ID,
                self::STUB_STORE_ID_GLOBAL,
                self::STUB_UPDATED_PRODUCT_NAME,
                self::STUB_PRODUCT_NAME,
            ],
        ];
    }
}
