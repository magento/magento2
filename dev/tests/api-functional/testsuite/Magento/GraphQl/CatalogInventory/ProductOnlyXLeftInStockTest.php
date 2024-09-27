<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\CatalogInventory;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Config\Model\ResourceModel\Config;
use Magento\ConfigurableProduct\Test\Fixture\Attribute as AttributeFixture;
use Magento\ConfigurableProduct\Test\Fixture\Product as ConfigurableProductFixture;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\DataObject;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\Quote\Test\Fixture\QuoteIdMask as QuoteMaskFixture;
use Magento\TestFramework\App\ApiMutableScopeConfig;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\CatalogInventory\Model\Configuration;

/**
 * Test for the product only x left in stock
 */
class ProductOnlyXLeftInStockTest extends GraphQlAbstract
{
    private const PARENT_SKU_CONFIGURABLE = 'parent_configurable';

    private const SKU = 'simple_10';

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;
    /**
     * @var Config $config
     */
    private $resourceConfig;

    /**
     * @var ApiMutableScopeConfig
     */
    private $scopeConfig;

    /**
     * @var ReinitableConfigInterface
     */
    private $reinitConfig;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $objectManager = ObjectManager::getInstance();
        $this->productRepository = $objectManager->create(ProductRepositoryInterface::class);
        $this->resourceConfig = $objectManager->get(Config::class);
        $this->scopeConfig = $objectManager->get(ApiMutableScopeConfig::class);
        $this->reinitConfig = $objectManager->get(ReinitableConfigInterface::class);
        $this->fixtures = DataFixtureStorageManager::getStorage();
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple_with_all_fields.php
     */
    public function testQueryProductOnlyXLeftInStockDisabled()
    {
        $productSku = 'simple';

        $query = <<<QUERY
        {
            products(filter: {sku: {eq: "{$productSku}"}})
            {
                items {
                    only_x_left_in_stock
                }
            }
        }
QUERY;

        $response = $this->graphQlQuery($query);

        $this->assertArrayHasKey(0, $response['products']['items']);
        $this->assertArrayHasKey('only_x_left_in_stock', $response['products']['items'][0]);
        $this->assertNull($response['products']['items'][0]['only_x_left_in_stock']);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple_with_all_fields.php
     * @magentoConfigFixture default_store cataloginventory/options/stock_threshold_qty 120
     */
    public function testQueryProductOnlyXLeftInStockEnabled()
    {
        $productSku = 'simple';

        $query = <<<QUERY
        {
            products(filter: {sku: {eq: "{$productSku}"}})
            {
                items {
                    only_x_left_in_stock
                }
            }
        }
QUERY;
        $response = $this->graphQlQuery($query);

        $this->assertArrayHasKey(0, $response['products']['items']);
        $this->assertArrayHasKey('only_x_left_in_stock', $response['products']['items'][0]);
        $this->assertEquals(100, $response['products']['items'][0]['only_x_left_in_stock']);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple_out_of_stock_without_categories.php
     * @magentoConfigFixture default_store cataloginventory/options/stock_threshold_qty 120
     */
    public function testQueryProductOnlyXLeftInStockOutstock()
    {
        $productSku = 'simple';
        $showOutOfStock = $this->scopeConfig->getValue(Configuration::XML_PATH_SHOW_OUT_OF_STOCK);

        $this->resourceConfig->saveConfig(Configuration::XML_PATH_SHOW_OUT_OF_STOCK, 1);
        $this->reinitConfig->reinit();

        // need to resave product to reindex it with new configuration.
        $product = $this->productRepository->get($productSku);
        $this->productRepository->save($product);

        $query = <<<QUERY
        {
            products(filter: {sku: {eq: "{$productSku}"}})
            {
                items {
                    only_x_left_in_stock
                }
            }
        }
QUERY;
        $response = $this->graphQlQuery($query);

        $this->resourceConfig->saveConfig(Configuration::XML_PATH_SHOW_OUT_OF_STOCK, $showOutOfStock);
        $this->reinitConfig->reinit();

        $this->assertArrayHasKey(0, $response['products']['items']);
        $this->assertArrayHasKey('only_x_left_in_stock', $response['products']['items'][0]);
        $this->assertEquals(0, $response['products']['items'][0]['only_x_left_in_stock']);
    }

    #[
        DataFixture(ProductFixture::class, ['sku' => self::SKU], as: 'product'),
        DataFixture(AttributeFixture::class, as: 'attribute'),
        DataFixture(
            ConfigurableProductFixture::class,
            [
                'sku' => self::PARENT_SKU_CONFIGURABLE,
                '_options' => ['$attribute$'],
                '_links' => ['$product$'],
            ],
            'configurable_product'
        ),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(QuoteMaskFixture::class, ['cart_id' => '$cart.id$'], 'quoteIdMask'),
    ]
    /**
     * @dataProvider stockThresholdQtyProvider
     */
    public function testOnlyXLeftInStockConfigurableProduct(string $stockThresholdQty, ?int $expected): void
    {
        $this->scopeConfig->setValue('cataloginventory/options/stock_threshold_qty', $stockThresholdQty);
        $maskedQuoteId = $this->fixtures->get('quoteIdMask')->getMaskedId();
        /** @var AttributeInterface $attribute */
        $attribute = $this->fixtures->get('attribute');
        /** @var AttributeOptionInterface $option */
        $option = $attribute->getOptions()[1];
        $selectedOption = base64_encode("configurable/{$attribute->getAttributeId()}/{$option->getValue()}");
        $query = $this->mutationAddConfigurableProduct(
            $maskedQuoteId,
            self::PARENT_SKU_CONFIGURABLE,
            $selectedOption,
            100
        );

        $this->graphQlMutation($query);

        $query = <<<QUERY
{
	cart(cart_id: "$maskedQuoteId") {
		total_quantity
		itemsV2 {
			items {
				uid
                ... on ConfigurableCartItem {
                      configured_variant {
                        name
                        sku
                        stock_status
                        only_x_left_in_stock
                    }
                }
			}
		}
	}
}
QUERY;

        $response = $this->graphQlQuery($query);
        $responseDataObject = new DataObject($response);
        self::assertEquals(
            $expected,
            $responseDataObject->getData('cart/itemsV2/items/0/configured_variant/only_x_left_in_stock'),
        );
    }

    public static function stockThresholdQtyProvider(): array
    {
        return [
            ['0', null],
            ['200', 100]
        ];
    }

    private function mutationAddConfigurableProduct(
        string $cartId,
        string $sku,
        string $selectedOption,
        int $qty = 1
    ): string {
        return <<<QUERY
mutation {
  addProductsToCart(
    cartId: "{$cartId}",
    cartItems: [
    {
      sku: "{$sku}"
      quantity: $qty
      selected_options: [
        "$selectedOption"
      ]
    }]
  ) {
    cart {
        id
    }
    user_errors {
      code
      message
    }
  }
}
QUERY;
    }
}
