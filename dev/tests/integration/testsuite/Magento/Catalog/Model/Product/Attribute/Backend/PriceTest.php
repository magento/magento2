<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Attribute\Backend;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product;
use Magento\Catalog\Observer\SwitchPriceAttributeScopeOnConfigChange;
use Magento\Catalog\Test\Fixture\Attribute as AttributeFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Store\Model\Store;
use Magento\Store\Test\Fixture\Group as StoreGroupFixture;
use Magento\Store\Test\Fixture\Store as StoreFixture;
use Magento\Store\Test\Fixture\Website as WebsiteFixture;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;

/**
 * Test class for \Magento\Catalog\Model\Product\Attribute\Backend\Price.
 *
 * @magentoAppArea adminhtml
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PriceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Backend\Price
     */
    private $model;

    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    private $objectManager;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /**
     * @var Product
     */
    private $productResource;

    /**
     * @var \Magento\TestFramework\Fixture\DataFixtureStorage
     */
    private $fixtures;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var ReinitableConfigInterface $reinitiableConfig */
        $reinitiableConfig = $this->objectManager->get(ReinitableConfigInterface::class);
        $reinitiableConfig->setValue(
            'catalog/price/scope',
            Store::PRICE_SCOPE_WEBSITE
        );
        $observer = $this->objectManager->get(\Magento\Framework\Event\Observer::class);
        $this->objectManager->get(SwitchPriceAttributeScopeOnConfigChange::class)
            ->execute($observer);

        $this->model = $this->objectManager->create(
            \Magento\Catalog\Model\Product\Attribute\Backend\Price::class
        );
        $this->productRepository = $this->objectManager->create(
            ProductRepositoryInterface::class
        );
        $this->productResource = $this->objectManager->create(
            Product::class
        );
        $this->fixtures = $this->objectManager->get(DataFixtureStorageManager::class)->getStorage();
        $this->model->setAttribute(
            $this->objectManager->get(
                \Magento\Eav\Model\Config::class
            )->getAttribute(
                'catalog_product',
                'price'
            )
        );
    }

    /**
     * @magentoDbIsolation disabled
     */
    public function testSetScopeDefault()
    {
        /* validate result of setAttribute */
        $this->assertEquals(
            \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
            $this->model->getAttribute()->getIsGlobal()
        );
        $this->model->setScope($this->model->getAttribute());
        $this->assertEquals(
            \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
            $this->model->getAttribute()->getIsGlobal()
        );
    }

    /**
     * @magentoDbIsolation disabled
     * @magentoConfigFixture current_store catalog/price/scope 1
     */
    public function testSetScope()
    {
        $this->model->setScope($this->model->getAttribute());
        $this->assertEquals(
            \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_WEBSITE,
            $this->model->getAttribute()->getIsGlobal()
        );
    }

    /**
     * @magentoDbIsolation disabled
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoConfigFixture current_store catalog/price/scope 1
     * @magentoConfigFixture current_store currency/options/base GBP
     */
    public function testAfterSave()
    {
        /** @var Store $store */
        $store = $this->objectManager->create(Store::class);
        $globalStoreId = $store->load('admin')->getId();
        $product = $this->productRepository->get('simple');
        $product->setPrice('9.99');
        $product->setStoreId($globalStoreId);
        $this->productResource->save($product);
        $product = $this->productRepository->get('simple', false, $globalStoreId, true);
        $this->assertEquals('9.990000', $product->getPrice());
    }

    #[
        AppArea('adminhtml'),
        DbIsolation(false),
        Config('catalog/price/scope', '1', 'store'),
        DataFixture(WebsiteFixture::class, as: 'website2'),
        DataFixture(StoreGroupFixture::class, ['website_id' => '$website2.id$'], 'store_group2'),
        DataFixture(StoreFixture::class, ['store_group_id' => '$store_group2.id$'], 'store2'),
        DataFixture(StoreFixture::class, ['store_group_id' => '$store_group2.id$'], 'store3'),
        DataFixture(ProductFixture::class, as: 'product'),
    ]
    public function testAfterSaveWithDifferentStores()
    {
        /** @var Store $store */
        $store = $this->objectManager->create(
            Store::class
        );
        $globalStoreId = $store->load('admin')->getId();
        $secondStoreId = $this->fixtures->get('store2')->getId();
        $thirdStoreId = $this->fixtures->get('store3')->getId();
        $productSku = $this->fixtures->get('product')->getSku();
        /** @var \Magento\Catalog\Model\Product\Action $productAction */
        $productAction = $this->objectManager->create(
            \Magento\Catalog\Model\Product\Action::class
        );

        $product = $this->productRepository->get($productSku);
        $productId = $product->getId();
        $productAction->updateWebsites([$productId], [$store->load('fixture_second_store')->getWebsiteId()], 'add');
        $product->setStoreId($secondStoreId);
        $product->setPrice('9.99');
        $this->productResource->save($product);

        $product = $this->productRepository->get($productSku, false, $globalStoreId, true);
        $this->assertEquals(10, $product->getPrice());

        $product = $this->productRepository->get($productSku, false, $secondStoreId, true);
        $this->assertEquals('9.990000', $product->getPrice());

        $product = $this->productRepository->get($productSku, false, $thirdStoreId, true);
        $this->assertEquals('9.990000', $product->getPrice());
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture Magento/Store/_files/second_website_with_two_stores.php
     * @magentoConfigFixture current_store catalog/price/scope 1
     * @magentoDbIsolation disabled
     * @magentoAppArea adminhtml
     */
    public function testAfterSaveWithSameCurrency()
    {
        /** @var Store $store */
        $store = $this->objectManager->create(
            Store::class
        );
        $globalStoreId = $store->load('admin')->getId();
        $secondStoreId = $store->load('fixture_second_store')->getId();
        $thirdStoreId = $store->load('fixture_third_store')->getId();
        /** @var \Magento\Catalog\Model\Product\Action $productAction */
        $productAction = $this->objectManager->create(
            \Magento\Catalog\Model\Product\Action::class
        );

        $product = $this->productRepository->get('simple');
        $productId = $product->getId();
        $productAction->updateWebsites([$productId], [$store->load('fixture_second_store')->getWebsiteId()], 'add');
        $product->setOrigData();
        $product->setStoreId($secondStoreId);
        $product->setPrice('9.99');
        $this->productResource->save($product);

        $product = $this->productRepository->get('simple', false, $globalStoreId, true);
        $this->assertEquals(10, $product->getPrice());

        $product = $this->productRepository->get('simple', false, $secondStoreId, true);
        $this->assertEquals('9.990000', $product->getPrice());

        $product = $this->productRepository->get('simple', false, $thirdStoreId, true);
        $this->assertEquals('9.990000', $product->getPrice());
    }

    /**
     * @magentoDbIsolation disabled
     * @magentoAppArea adminhtml
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture Magento/Store/_files/second_website_with_two_stores.php
     * @magentoConfigFixture current_store catalog/price/scope 1
     */
    public function testAfterSaveWithUseDefault()
    {
        /** @var Store $store */
        $store = $this->objectManager->create(
            Store::class
        );
        $globalStoreId = $store->load('admin')->getId();
        $secondStoreId = $store->load('fixture_second_store')->getId();
        $thirdStoreId = $store->load('fixture_third_store')->getId();
        /** @var \Magento\Catalog\Model\Product\Action $productAction */
        $productAction = $this->objectManager->create(
            \Magento\Catalog\Model\Product\Action::class
        );

        $product = $this->productRepository->get('simple');
        $productId = $product->getId();
        $productAction->updateWebsites([$productId], [$store->load('fixture_second_store')->getWebsiteId()], 'add');
        $product->setOrigData();
        $product->setStoreId($secondStoreId);
        $product->setPrice('9.99');
        $this->productResource->save($product);

        $product = $this->productRepository->get('simple', false, $globalStoreId, true);
        $this->assertEquals(10, $product->getPrice());

        $product = $this->productRepository->get('simple', false, $secondStoreId, true);
        $this->assertEquals('9.990000', $product->getPrice());

        $product = $this->productRepository->get('simple', false, $thirdStoreId, true);
        $this->assertEquals('9.990000', $product->getPrice());

        $product->setStoreId($thirdStoreId);
        $product->setPrice(null);
        $this->productResource->save($product);

        $product = $this->productRepository->get('simple', false, $globalStoreId, true);
        $this->assertEquals(10, $product->getPrice());

        $product = $this->productRepository->get('simple', false, $secondStoreId, true);
        $this->assertEquals(10, $product->getPrice());

        $product = $this->productRepository->get('simple', false, $thirdStoreId, true);
        $this->assertEquals(10, $product->getPrice());
    }

    /**
     * @magentoDbIsolation disabled
     * @magentoAppArea adminhtml
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture Magento/Store/_files/second_website_with_two_stores.php
     * @magentoConfigFixture default_store catalog/price/scope 1
     */
    public function testAfterSaveForWebsitesWithDifferentCurrencies()
    {
        /** @var Store $store */
        $store = $this->objectManager->create(
            Store::class
        );

        /** @var \Magento\Directory\Model\ResourceModel\Currency $rate */
        $rate = $this->objectManager->create(\Magento\Directory\Model\ResourceModel\Currency::class);
        $rate->saveRates([
            'USD' => ['EUR' => 2],
            'EUR' => ['USD' => 0.5]
        ]);

        $globalStoreId = $store->load('admin')->getId();
        $secondStore = $store->load('fixture_second_store');
        $secondStoreId = $store->load('fixture_second_store')->getId();
        $thirdStoreId = $store->load('fixture_third_store')->getId();

        /** @var \Magento\Framework\App\Config\ReinitableConfigInterface $config */
        $config = $this->objectManager->get(\Magento\Framework\App\Config\MutableScopeConfigInterface::class);
        $config->setValue(
            'currency/options/default',
            'EUR',
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITES,
            'test'
        );

        $productAction = $this->objectManager->create(
            \Magento\Catalog\Model\Product\Action::class
        );
        $product = $this->productRepository->get('simple');
        $productId = $product->getId();
        $productAction->updateWebsites([$productId], [$secondStore->getWebsiteId()], 'add');
        $product->setOrigData();
        $product->setStoreId($globalStoreId);
        $product->setPrice(100);
        $this->productResource->save($product);

        $product = $this->productRepository->get('simple', false, $globalStoreId, true);
        $this->assertEquals(100, $product->getPrice());

        $product = $this->productRepository->get('simple', false, $secondStoreId, true);
        $this->assertEquals(100, $product->getPrice());

        $product = $this->productRepository->get('simple', false, $thirdStoreId, true);
        $this->assertEquals(100, $product->getPrice());
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        /** @var ReinitableConfigInterface $reinitiableConfig */
        $reinitiableConfig = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            ReinitableConfigInterface::class
        );
        $reinitiableConfig->setValue(
            'catalog/price/scope',
            Store::PRICE_SCOPE_GLOBAL
        );
        $observer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\Event\Observer::class
        );
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(SwitchPriceAttributeScopeOnConfigChange::class)
            ->execute($observer);
    }

    /**
     * @dataProvider saveCustomPriceAttributeDataProvider
     */
    #[
        AppArea('adminhtml'),
        DbIsolation(false),
        Config('catalog/price/scope', '1', 'store'),
        DataFixture(WebsiteFixture::class, as: 'website2'),
        DataFixture(StoreGroupFixture::class, ['website_id' => '$website2.id$'], 'store_group2'),
        DataFixture(StoreFixture::class, ['store_group_id' => '$store_group2.id$'], 'store2'),
        DataFixture(StoreFixture::class, ['store_group_id' => '$store_group2.id$'], 'store3'),
        DataFixture(AttributeFixture::class, ['frontend_input' => 'price', 'is_filterable' => 1], 'attr1'),
        DataFixture(AttributeFixture::class, ['frontend_input' => 'price', 'is_filterable' => 1], 'attr2'),
        DataFixture(AttributeFixture::class, ['frontend_input' => 'price', 'is_filterable' => 1], 'attr3'),
        DataFixture(ProductFixture::class, ['website_ids' => [1, '$website2.id']], 'product'),
    ]
    public function testSaveCustomPriceAttribute(
        array $attributes,
        array $updates,
        array $expectedValues,
        array $expectedIndexValues
    ) {
        $storeIds['admin'] = $this->objectManager->create(Store::class)->load('admin')->getId();
        $storeIds['default'] = $this->objectManager->create(Store::class)->load('default')->getId();
        $storeIds['store2'] = $this->fixtures->get('store2')->getId();
        $storeIds['store3'] = $this->fixtures->get('store3')->getId();
        $storeNames = array_flip($storeIds);
        $productSku = $this->fixtures->get('product')->getSku();
        $productId = $this->fixtures->get('product')->getId();

        foreach ($updates as $name => $scopes) {
            $attributeCode = $this->fixtures->get($name)->getAttributeCode();
            foreach ($scopes as $storeName => $storeValue) {
                $product = $this->productRepository->get($productSku, true, $storeIds[$storeName], true);
                $product->setData($attributeCode, $storeValue);
                $this->productResource->save($product);
            }
        }

        $actualValues = [];
        foreach ($attributes as $name) {
            $attributeCode = $this->fixtures->get($name)->getAttributeCode();
            foreach ($storeIds as $storeName => $storeId) {
                $product = $this->productRepository->get($productSku, false, $storeId, true);
                $actualValues[$name][$storeName] = $product->getData($attributeCode);
            }
        }

        $this->assertEquals($expectedValues, $actualValues);

        $connection = $this->productResource->getConnection();

        $actualIndexValues = [];
        foreach ($attributes as $name) {
            $attributeId = $this->fixtures->get($name)->getId();
            $select = $connection->select()
                ->from(
                    $connection->getTableName('catalog_product_index_eav_decimal'),
                    [
                        'store_id',
                        'value'
                    ]
                )
                ->where(
                    'entity_id = ?',
                    $productId,
                )
                ->where(
                    'attribute_id = ?',
                    $attributeId
                );
            $actualIndexValues[$name] = [];
            foreach ($connection->fetchPairs($select) as $storeId => $storeValue) {
                $actualIndexValues[$name][$storeNames[$storeId]] = $storeValue;
            }
        }

        $this->assertEquals($expectedIndexValues, $actualIndexValues);
    }

    /**
     * @return array[]
     */
    public static function saveCustomPriceAttributeDataProvider(): array
    {
        return [
            [
                'attributes' => ['attr1', 'attr2', 'attr3'],
                'updates' => [
                    'attr1' => [
                        'admin' => 9,
                    ],
                    'attr2' => [
                        'admin' => 7,
                        'store2' => 3.5,
                    ],
                    'attr3' => [
                        'store3' => 15,
                    ]
                ],
                'expectedValues' =>[
                    'attr1' => [
                        'admin' => 9,
                        'default' => 9,
                        'store2' => 9,
                        'store3' => 9,
                    ],
                    'attr2' => [
                        'admin' => 7,
                        'default' => 7,
                        'store2' => 3.5,
                        'store3' => 3.5,
                    ],
                    'attr3' => [
                        'admin' => null,
                        'default' => null,
                        'store2' => 15,
                        'store3' => 15,
                    ]
                ],
                'expectedIndexValues' => [
                    'attr1' => [
                        'default' => 9,
                        'store2' => 9,
                        'store3' => 9,
                    ],
                    'attr2' => [
                        'default' => 7,
                        'store2' => 3.5,
                        'store3' => 3.5,
                    ],
                    'attr3' => [
                        'store2' => 15,
                        'store3' => 15,
                    ]
                ]
            ]
        ];
    }
}
