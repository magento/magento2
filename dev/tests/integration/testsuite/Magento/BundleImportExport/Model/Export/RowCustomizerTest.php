<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\BundleImportExport\Model\Export;

use Magento\Bundle\Test\Fixture\Option as BundleOptionFixture;
use Magento\Bundle\Test\Fixture\Link as BundleSelectionFixture;
use Magento\Bundle\Test\Fixture\Product as BundleProductFixture;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Helper\Data;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Test\Fixture\Group as GroupFixture;
use Magento\Store\Test\Fixture\Store as StoreFixture;
use Magento\Store\Test\Fixture\Website as WebsiteFixture;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Fixture\ScopeFixture;

/**
 * @magentoAppArea adminhtml
 */
class RowCustomizerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\BundleImportExport\Model\Export\RowCustomizer
     */
    private $model;
    
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;
    
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    
    /**
     * @var int
     */
    private $currentStoreId;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->model = $this->objectManager->create(
            \Magento\BundleImportExport\Model\Export\RowCustomizer::class
        );
        $this->productRepository = $this->objectManager->get(
            \Magento\Catalog\Api\ProductRepositoryInterface::class
        );
        $this->storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $this->currentStoreId = $this->storeManager->getStore()->getId();
    }
    
    protected function tearDown(): void
    {
        $this->storeManager->setCurrentStore($this->currentStoreId);
        parent::tearDown();
    }

    /**
     * @magentoDataFixture Magento/Bundle/_files/product.php
     * @magentoDbIsolation disabled
     *
     * @return void
     */
    public function testPrepareData(): void
    {
        $parsedAdditionalAttributes = 'text_attribute=!@#$%^&*()_+1234567890-=|\\:;"\'<,>.?/'
            . ',text_attribute2=,';
        $allAdditionalAttributes = $parsedAdditionalAttributes . ',weight_type=0,price_type=1';
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $this->objectManager->get(\Magento\Catalog\Model\ResourceModel\Product\Collection::class);
        $select = $collection->getConnection()->select()
            ->from(['p' => $collection->getTable('catalog_product_entity')], ['sku', 'entity_id'])
            ->where('sku IN(?)', ['simple', 'custom-design-simple-product', 'bundle-product']);
        $ids = $collection->getConnection()->fetchPairs($select);
        $select = (string)$collection->getSelect();
        $this->model->prepareData($collection, array_values($ids));
        $this->assertEquals($select, (string)$collection->getSelect());
        $result = $this->model->addData(['additional_attributes' => $allAdditionalAttributes], $ids['bundle-product']);
        $this->assertArrayHasKey('bundle_price_type', $result);
        $this->assertArrayHasKey('bundle_shipment_type', $result);
        $this->assertArrayHasKey('bundle_sku_type', $result);
        $this->assertArrayHasKey('bundle_price_view', $result);
        $this->assertArrayHasKey('bundle_weight_type', $result);
        $this->assertArrayHasKey('bundle_values', $result);
        $this->assertStringContainsString('sku=simple,', $result['bundle_values']);
        $this->assertEquals([], $this->model->addData([], $ids['simple']));
        $this->assertEquals($parsedAdditionalAttributes, $result['additional_attributes']);
    }

    /**
     * @magentoDataFixture Magento/Store/_files/second_store.php
     * @magentoDataFixture Magento/Bundle/_files/product.php
     * @magentoDbIsolation disabled
     *
     * @return void
     */
    public function testPrepareDataWithDifferentStoreValues(): void
    {
        $storeCode = 'default';
        $expectedNames = [
            'name' => 'Bundle Product Items',
            'name_' . $storeCode => 'Bundle Product Items_' . $storeCode,
        ];
        $parsedAdditionalAttributes = 'text_attribute=!@#$%^&*()_+1234567890-=|\\:;"\'<,>.?/'
            . ',text_attribute2=,';
        $allAdditionalAttributes = $parsedAdditionalAttributes . ',weight_type=0,price_type=1';
        $collection = $this->objectManager->get(\Magento\Catalog\Model\ResourceModel\Product\Collection::class);
        /** @var \Magento\Store\Model\Store $store */
        $store = $this->objectManager->create(\Magento\Store\Model\Store::class);
        $store->load($storeCode, 'code');
        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $product = $productRepository->get('bundle-product', 1, $store->getId());

        $extension = $product->getExtensionAttributes();
        $options = $extension->getBundleProductOptions();

        foreach ($options as $productOption) {
            $productOption->setTitle($productOption->getTitle() . '_' . $store->getCode());
        }
        $extension->setBundleProductOptions($options);
        $product->setExtensionAttributes($extension);
        $productRepository->save($product);
        $this->model->prepareData($collection, [$product->getId()]);
        $result = $this->model->addData(['additional_attributes' => $allAdditionalAttributes], $product->getId());
        $bundleValues = array_map(
            function ($input) {
                $data = explode('=', $input);

                return [$data[0] => $data[1]];
            },
            explode(',', $result['bundle_values'])
        );
        $actualNames = [
            'name' => array_column($bundleValues, 'name')[0],
            'name' . '_' . $store->getCode() => array_column($bundleValues, 'name' . '_' . $store->getCode())[0],
        ];

        self::assertSame($expectedNames, $actualNames);
    }

    #[
        // export is performed in global scope
        AppArea('global'),
        DbIsolation(false),
        Config(Data::XML_PATH_PRICE_SCOPE, 1, ScopeInterface::SCOPE_STORE, 'default'),
        Config(Data::XML_PATH_PRICE_SCOPE, 1, ScopeInterface::SCOPE_STORE, 'bundle_test_store2'),
        DataFixture(ScopeFixture::class, as: 'global_scope'),
        DataFixture(WebsiteFixture::class, as: 'website2'),
        DataFixture(GroupFixture::class, ['website_id' => '$website2.id$'], 'group2'),
        DataFixture(StoreFixture::class, ['store_group_id' => '$group2.id$', 'code' => 'bundle_test_store2'], 'store2'),
        DataFixture(ProductFixture::class, as: 'p1'),
        DataFixture(ProductFixture::class, as: 'p2'),
        DataFixture(BundleSelectionFixture::class, ['sku' => '$p1.sku$', 'price' => 10, 'price_type' => 0], 'link1'),
        DataFixture(BundleSelectionFixture::class, ['sku' => '$p2.sku$', 'price' => 20, 'price_type' => 1], 'link2'),
        DataFixture(BundleOptionFixture::class, ['product_links' => ['$link1$']], 'opt1'),
        DataFixture(BundleOptionFixture::class, ['product_links' => ['$link2$']], 'opt2'),
        DataFixture(
            BundleProductFixture::class,
            ['price' => 50,'price_type' => 1, 'website_ids' => [1, '$website2.id$'], '_options' => ['$opt1$','$opt2$']],
            'bundle',
            'global_scope',
        ),
    ]
    public function testExportWhenPriceScopeIsWebsite(): void
    {
        $fixtures = DataFixtureStorageManager::getStorage();
        $bundleProduct = $fixtures->get('bundle');
        $sku1 = $fixtures->get('p1')->getSku();
        $sku2 = $fixtures->get('p2')->getSku();
        $opt1 = $fixtures->get('opt1')->getTitle();
        $opt2 = $fixtures->get('opt2')->getTitle();
        $websiteCode = $fixtures->get('website2')->getCode();
        $store = $fixtures->get('store2');

        $data['bundle_values'] = "name=$opt1,type=select,required=1,sku=$sku1,price=10.0000" .
            ",default=0,default_qty=1.0000,price_type=fixed,can_change_qty=0" .
            "|name=$opt2,type=select,required=1,sku=$sku2,price=20.0000" .
            ",default=0,default_qty=1.0000,price_type=percent,can_change_qty=0";
        $this->assertBundleValues($data, $bundleProduct);

        // Update selection prices in second store
        $bundleProduct = $this->productRepository->get($bundleProduct->getSku(), true);
        $this->storeManager->setCurrentStore($store->getId());
        $bundleProduct->setStoreId($store->getId());
        $extension = $bundleProduct->getExtensionAttributes();
        $options = $extension->getBundleProductOptions();
        $options[0]->getProductLinks()[0]->setPrice(40);
        $options[0]->getProductLinks()[0]->setPriceType(1);
        $options[1]->getProductLinks()[0]->setPrice(50);
        $options[1]->getProductLinks()[0]->setPriceType(1);
        $this->productRepository->save($bundleProduct);
        $this->storeManager->setCurrentStore($this->currentStoreId);

        $data['bundle_values'] = "name=$opt1,type=select,required=1,sku=$sku1,price=10.0000" .
            ",default=0,default_qty=1.0000,price_type=fixed,can_change_qty=0" .
            ",price_website_$websiteCode=40.000000,price_type_website_$websiteCode=percent" .
            "|name=$opt2,type=select,required=1,sku=$sku2,price=20.0000" .
            ",default=0,default_qty=1.0000,price_type=percent,can_change_qty=0" .
            ",price_website_$websiteCode=50.000000,price_type_website_$websiteCode=percent";
        $this->assertBundleValues($data, $bundleProduct);
    }

    private function assertBundleValues(array $expected, ProductInterface $product): void
    {
        $collection = $this->objectManager->create(\Magento\Catalog\Model\ResourceModel\Product\Collection::class);
        $this->model->prepareData($collection, [$product->getId()]);
        $actual = $this->model->addData([], $product->getId());
        $this->assertEquals($expected, array_intersect_key($actual, $expected));
    }
}
