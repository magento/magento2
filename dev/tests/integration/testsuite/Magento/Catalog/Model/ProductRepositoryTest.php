<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Media\ConfigInterface;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Catalog\Test\Fixture\AttributeSet as AttributeSetFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Customer\Model\Group;
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
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Test\Fixture\Group as StoreGroupFixture;
use Magento\Store\Test\Fixture\Store as StoreFixture;
use Magento\Store\Test\Fixture\Website as WebsiteFixture;
use Magento\TestFramework\Catalog\Model\ProductLayoutUpdateManager;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
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
    private const STUB_STORE_ID = 1;
    private const STUB_STORE_ID_GLOBAL = 0;
    private const STUB_PRODUCT_NAME = 'Simple Product';
    private const STUB_UPDATED_PRODUCT_NAME = 'updated';
    private const STUB_PRODUCT_SKU = 'simple';

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
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var int
     */
    private $currentStore;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        Bootstrap::getObjectManager()->configure([
            'preferences' => [
                \Magento\Catalog\Model\Product\Attribute\LayoutUpdateManager::class =>
                    \Magento\TestFramework\Catalog\Model\ProductLayoutUpdateManager::class
            ]
        ]);
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
        $this->fixtures = $this->objectManager->get(DataFixtureStorageManager::class)->getStorage();
        $this->storeManager = $this->objectManager->create(StoreManagerInterface::class);
        $this->currentStore = $this->storeManager->getStore()->getId();
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

        $this->storeManager->setCurrentStore($this->currentStore);
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

    /**
     * @magentoDataFixture setPriceScopeToWebsite
     */
    #[
        AppArea('adminhtml'),
        DbIsolation(false),
        DataFixture(WebsiteFixture::class, as: 'website2'),
        DataFixture(StoreGroupFixture::class, ['website_id' => '$website2.id$'], 'store_group2'),
        DataFixture(StoreFixture::class, ['store_group_id' => '$store_group2.id$'], 'store2'),
        DataFixture(ProductFixture::class, ['website_ids' => [1, '$website2.id']], 'product1'),
        DataFixture(ProductFixture::class, ['website_ids' => [1, '$website2.id']], 'product2'),
    ]
    public function testConsecutivePartialProductsUpdateInStoreView(): void
    {
        $store1 = $this->storeManager->getStore('default')->getId();
        $store2 = $this->fixtures->get('store2')->getId();
        $product1 = $this->fixtures->get('product1');
        $product2 = $this->fixtures->get('product2');
        $product1Store1Name = $product1->getName();
        $product2Store1Name = $product2->getName();
        $product2Store1Price = 10;

        $product1Store2Name = $product1->getName() . ' Store View Value';
        $product2Store2Name = $product2->getName() . ' Store View Value';
        $product2Store2Price = 9;

        $this->storeManager->setCurrentStore($store2);
        $this->productRepository->save(
            $this->getProductInstance(
                [
                    'sku' => $product2->getSku(),
                    'price' => $product2Store2Price,
                ]
            )
        );
        $this->productRepository->save(
            $this->getProductInstance(
                [
                    'sku' => $product1->getSku(),
                    'name' => $product1Store2Name,
                ]
            )
        );
        $this->productRepository->save(
            $this->getProductInstance(
                [
                    'sku' => $product2->getSku(),
                    'name' => $product2Store2Name,
                ]
            )
        );
        $product1 = $this->productRepository->get($product1->getSku(), true, $store2, true);
        $product2 = $this->productRepository->get($product2->getSku(), true, $store2, true);
        $this->assertEquals($product1Store2Name, $product1->getName());
        $this->assertEquals($product2Store2Name, $product2->getName());
        $this->assertEquals($product2Store2Price, $product2->getPrice());

        $this->storeManager->setCurrentStore($store1);

        $product1 = $this->productRepository->get($product1->getSku(), true, $store1, true);
        $product2 = $this->productRepository->get($product2->getSku(), true, $store1, true);
        $this->assertEquals($product1Store1Name, $product1->getName());
        $this->assertEquals($product2Store1Name, $product2->getName());
        $this->assertEquals($product2Store1Price, $product2->getPrice());
    }

    #[
        AppArea('adminhtml'),
        DataFixture(AttributeSetFixture::class, as: 'attribute_set2'),
        DataFixture(
            ProductFixture::class,
            [
                'tier_prices' => [
                    [
                        'customer_group_id' => Group::NOT_LOGGED_IN_ID,
                        'qty' => 2,
                        'value' => 7.5
                    ]
                ]
            ],
            'product1'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'attribute_set_id' => '$attribute_set2.attribute_set_id$',
                'tier_prices' => [
                    [
                        'customer_group_id' => Group::NOT_LOGGED_IN_ID,
                        'qty' => 4,
                        'value' => 8
                    ]
                ]
            ],
            'product2'
        ),
    ]
    public function testConsecutiveProductsUpdateWithDifferentAttributeSets(): void
    {
        $product1 = $this->fixtures->get('product1');
        $product2 = $this->fixtures->get('product2');
        $store1 = $this->storeManager->getStore('default')->getId();
        $this->storeManager->setCurrentStore($store1);
        $product1UpdatedName = $product1->getName() . ' for default store view';
        $product2UpdatedName = $product2->getName() . ' for default store view';
        $this->productRepository->save(
            $this->getProductInstance(
                [
                    'sku' => $product1->getSku(),
                    'name' => $product1UpdatedName,
                ]
            )
        );
        $this->productRepository->save(
            $this->getProductInstance(
                [
                    'sku' => $product2->getSku(),
                    'name' => $product2UpdatedName,
                ]
            )
        );
        $product1 = $this->productRepository->get($product1->getSku(), true, $store1, true);
        $this->assertEquals($product1UpdatedName, $product1->getName());
        $this->assertCount(1, $product1->getTierPrices());
        $this->assertEquals(
            [
                'customer_group_id' => Group::NOT_LOGGED_IN_ID,
                'qty' => 2,
                'value' => 7.5
            ],
            [
                'customer_group_id' => $product1->getTierPrices()[0]->getCustomerGroupId(),
                'qty' => $product1->getTierPrices()[0]->getQty(),
                'value' => $product1->getTierPrices()[0]->getValue()
            ]
        );

        $product2 = $this->productRepository->get($product2->getSku(), true, $store1, true);
        $this->assertEquals($product2UpdatedName, $product2->getName());
        $this->assertCount(1, $product2->getTierPrices());
        $this->assertEquals(
            [
                'customer_group_id' => Group::NOT_LOGGED_IN_ID,
                'qty' => 4,
                'value' => 8
            ],
            [
                'customer_group_id' => $product2->getTierPrices()[0]->getCustomerGroupId(),
                'qty' => $product2->getTierPrices()[0]->getQty(),
                'value' => $product2->getTierPrices()[0]->getValue()
            ]
        );
    }

    /**
     * Get Simple Product Data
     *
     * @param array $data
     * @return ProductInterface
     */
    private function getProductInstance(array $data = []): ProductInterface
    {
        return $this->objectManager->create(
            ProductInterface::class,
            [
                'data' => $data
            ]
        );
    }

    /**
     * @return void
     */
    public static function setPriceScopeToWebsite(): void
    {
        self::setConfig(['catalog/price/scope' => 1]);
    }

    /**
     * @return void
     */
    public static function setPriceScopeToWebsiteRollback(): void
    {
        self::setConfig(['catalog/price/scope' => null]);
    }

    /**
     * @param array $config
     * @return void
     */
    private static function setConfig(array $config): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $configFactory = $objectManager->create(\Magento\Config\Model\Config\Factory::class);
        foreach ($config as $path => $value) {
            $inherit = $value === null;
            $pathParts = explode('/', $path);
            $store = 0;
            $configData = [
                'section' => $pathParts[0],
                'website' => '',
                'store' => $store,
                'groups' => [
                    $pathParts[1] => [
                        'fields' => [
                            $pathParts[2] => [
                                'value' => $value,
                                'inherit' => $inherit
                            ]
                        ]
                    ]
                ]
            ];

            $configFactory->create(['data' => $configData])->save();
        }
    }
}
