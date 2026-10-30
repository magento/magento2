<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Block\Adminhtml\Product\Composite\Fieldset;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Test\Fixture\PriceScope as PriceScopeFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\ConfigurableProduct\Test\Fixture\Attribute as ConfigurableAttributeFixture;
use Magento\ConfigurableProduct\Test\Fixture\Product as ConfigurableProductFixture;
use Magento\Directory\Model\Currency;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Store\Model\Store;
use Magento\Store\Test\Fixture\Group as GroupFixture;
use Magento\Store\Test\Fixture\Store as StoreFixture;
use Magento\Store\Test\Fixture\Website as WebsiteFixture;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test Configurable block in composite product configuration layout
 *
 * @see \Magento\ConfigurableProduct\Block\Adminhtml\Product\Composite\Fieldset\Configurable
 * @magentoAppArea adminhtml
 */
class ConfigurableTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var SerializerInterface */
    private $serializer;

    /** @var Configurable */
    private $block;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var Registry */
    private $registry;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManager = Bootstrap::getObjectManager();
        $this->serializer = $this->objectManager->get(SerializerInterface::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->productRepository->cleanCache();
        $this->block = $this->objectManager->get(LayoutInterface::class)->createBlock(Configurable::class);
        $this->registry = $this->objectManager->get(Registry::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->registry->unregister('product');

        parent::tearDown();
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple_duplicated.php
     * @return void
     */
    public function testGetProduct(): void
    {
        $product = $this->productRepository->get('simple-1');
        $this->registerProduct($product);
        $blockProduct = $this->block->getProduct();
        $this->assertSame($product, $blockProduct);
        $this->assertEquals(
            $product->getId(),
            $blockProduct->getId(),
            'The expected product is missing in the Configurable block!'
        );
        $this->assertNotNull($blockProduct->getTypeInstance()->getStoreFilter($blockProduct));
    }

    /**
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_products.php
     * @return void
     */
    public function testGetJsonConfig(): void
    {
        $product = $this->productRepository->get('configurable');
        $this->registerProduct($product);
        $config = $this->serializer->unserialize($this->block->getJsonConfig());
        $this->assertTrue($config['disablePriceReload']);
        $this->assertTrue($config['stablePrices']);
    }

    #[
        DbIsolation(false),
        Config(Currency::XML_PATH_CURRENCY_DEFAULT, 'EUR', 'store', 'store_view_2_euro'),
        Config(Store::XML_PATH_PRICE_SCOPE, Store::PRICE_SCOPE_WEBSITE),
        Config(Store::XML_PATH_PRICE_SCOPE, Store::PRICE_SCOPE_WEBSITE, 'store', 'default'),
        Config(Store::XML_PATH_PRICE_SCOPE, Store::PRICE_SCOPE_WEBSITE, 'store', 'store_view_2_euro'),
        DataFixture(WebsiteFixture::class, as: 'website_2'),
        DataFixture(GroupFixture::class, ['website_id' => '$website_2.id$'], as: 'store_2'),
        DataFixture(
            StoreFixture::class,
            ['store_group_id' => '$store_2.id$', 'code' => 'store_view_2_euro'],
            as: 'store_view_2'
        ),
        DataFixture(PriceScopeFixture::class, ['scope' => 'website']),
        DataFixture(
            ProductFixture::class,
            [
                'price' => 12,
                'website_ids' => [1, '$website_2.id$']
            ],
            'configurable_product_child'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => '$configurable_product_child.sku$',
                'price' => 5,
                '_update' => true
            ],
            scope: 'store_view_2'
        ),
        DataFixture(ConfigurableAttributeFixture::class, as: 'attribute'),
        DataFixture(
            ConfigurableProductFixture::class,
            [
                'website_ids' => [1, '$website_2.id$'],
                '_options' => ['$attribute$'],
                '_links' => ['$configurable_product_child$']
            ],
            'configurable_product'
        )
    ]
    public function testGetJsonConfigNonDefaultStore(): void
    {
        $fixtures = DataFixtureStorageManager::getStorage();
        $configurableProductSku = $fixtures->get('configurable_product')->getSku();
        $configurableChildProductId = $fixtures->get('configurable_product_child')->getId();
        $storeView2 = $fixtures->get('store_view_2');
        // Load configurable product in store view 1
        $configurableProduct = $this->productRepository->get($configurableProductSku, true, 1, true);
        $this->registerProduct($configurableProduct);
        $config = $this->serializer->unserialize($this->block->getJsonConfig());
        $this->assertNotEmpty($config);
        $this->assertEquals('$%s', $config['priceFormat']['pattern']);
        $this->assertEquals(12, $config['optionPrices'][$configurableChildProductId]['basePrice']['amount']);
        // Load configurable product in store view 2
        $configurableProduct = $this->productRepository->get($configurableProductSku, true, $storeView2->getId(), true);
        $this->registerProduct($configurableProduct);
        $config = $this->serializer->unserialize($this->block->getJsonConfig());
        $this->assertNotEmpty($config);
        $this->assertEquals('€%s', $config['priceFormat']['pattern']);
        $this->assertEquals(5, $config['optionPrices'][$configurableChildProductId]['basePrice']['amount']);
    }

    /**
     * Register the product
     *
     * @param ProductInterface $product
     * @return void
     */
    private function registerProduct(ProductInterface $product): void
    {
        $this->block->unsetData(['product', 'allow_products']);
        $this->registry->unregister('product');
        $this->registry->register('product', $product);
    }
}
