<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\CatalogInventory;

use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\Bundle\Test\Fixture\AddProductToCart as AddBundleProductToCart;
use Magento\Bundle\Test\Fixture\Link as BundleSelectionFixture;
use Magento\Bundle\Test\Fixture\Option as BundleOptionFixture;
use Magento\Bundle\Test\Fixture\Product as BundleProductFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Catalog\Test\Fixture\ProductStock as ProductStockFixture;
use Magento\ConfigurableProduct\Test\Fixture\AddProductToCart as AddConfigurableProductToCartFixture;
use Magento\ConfigurableProduct\Test\Fixture\Attribute as AttributeFixture;
use Magento\ConfigurableProduct\Test\Fixture\Product as ConfigurableProductFixture;
use Magento\Framework\DataObject;
use Magento\Quote\Test\Fixture\AddProductToCart;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\Quote\Test\Fixture\QuoteIdMask as QuoteMaskFixture;

/**
 * Test for product status
 */
class ProductStockStatusTest extends GraphQlAbstract
{
    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @throws LocalizedException
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->fixtures = DataFixtureStorageManager::getStorage();
    }

    /**
     * Test stock_status for unavailable simple product
     *
     * @throws Exception
     */
    #[
        DataFixture(ProductFixture::class, ['price' => 100.00], as: 'product'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(AddProductToCart::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 100]),
        DataFixture(QuoteMaskFixture::class, ['cart_id' => '$cart.id$'], 'quoteIdMask'),
        DataFixture(ProductStockFixture::class, ['prod_id' => '$product.id$', 'is_in_stock' => 0], 'prodStock')
    ]
    public function testStockStatusUnavailableSimpleProduct(): void
    {
        $maskedQuoteId = $this->fixtures->get('quoteIdMask')->getMaskedId();
        $query = $this->getQuery($maskedQuoteId);
        $response = $this->graphQlMutation($query);
        $responseDataObject = new DataObject($response);

        self::assertEquals(
            'OUT_OF_STOCK',
            $responseDataObject->getData('cart/itemsV2/items/0/product/stock_status')
        );
    }

    /**
     * Test stock_status for available simple product
     *
     * @throws Exception
     */
    #[
        DataFixture(ProductFixture::class, ['price' => 100.00], as: 'product'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(AddProductToCart::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 100]),
        DataFixture(QuoteMaskFixture::class, ['cart_id' => '$cart.id$'], 'quoteIdMask')
    ]
    public function testStockStatusAvailableSimpleProduct(): void
    {
        $maskedQuoteId = $this->fixtures->get('quoteIdMask')->getMaskedId();
        $query = $this->getQuery($maskedQuoteId);
        $response = $this->graphQlMutation($query);
        $responseDataObject = new DataObject($response);

        self::assertEquals(
            'IN_STOCK',
            $responseDataObject->getData('cart/itemsV2/items/0/product/stock_status')
        );
    }

    /**
     * Test stock_status for unavailable bundle product
     *
     * @throws Exception
     */
    #[
        DataFixture(ProductFixture::class, ['price' => 100.00], as: 'product'),
        DataFixture(
            BundleSelectionFixture::class,
            [
                'sku' => '$product.sku$', 'price' => 100, 'price_type' => 0
            ],
            as:'link'
        ),
        DataFixture(BundleOptionFixture::class, ['title' => 'Checkbox Options', 'type' => 'checkbox',
            'required' => 1,'product_links' => ['$link$']], 'option'),
        DataFixture(
            BundleProductFixture::class,
            ['price' => 90, '_options' => ['$option$']],
            as:'bundleProduct'
        ),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(
            AddBundleProductToCart::class,
            [
                'cart_id' => '$cart.id$',
                'product_id' => '$bundleProduct.id$',
                'selections' => [['$product.id$']],
                'qty' => 100
            ],
        ),
        DataFixture(QuoteMaskFixture::class, ['cart_id' => '$cart.id$'], 'quoteIdMask'),
        DataFixture(ProductStockFixture::class, ['prod_id' => '$product.id$', 'is_in_stock' => 0], 'prodStock')
    ]
    public function testStockStatusUnavailableBundleProduct(): void
    {
        $maskedQuoteId = $this->fixtures->get('quoteIdMask')->getMaskedId();
        $query = $this->getQuery($maskedQuoteId);
        $response = $this->graphQlMutation($query);
        $responseDataObject = new DataObject($response);

        self::assertEquals(
            'OUT_OF_STOCK',
            $responseDataObject->getData('cart/itemsV2/items/0/product/stock_status')
        );
    }

    /**
     * Test stock_status for available bundle product
     *
     * @throws Exception
     */
    #[
        DataFixture(ProductFixture::class, ['price' => 100.00], as: 'product'),
        DataFixture(
            BundleSelectionFixture::class,
            [
                'sku' => '$product.sku$', 'price' => 100, 'price_type' => 0
            ],
            as:'link'
        ),
        DataFixture(BundleOptionFixture::class, ['title' => 'Checkbox Options', 'type' => 'checkbox',
            'required' => 1,'product_links' => ['$link$']], 'option'),
        DataFixture(
            BundleProductFixture::class,
            ['price' => 90, '_options' => ['$option$']],
            as:'bundleProduct'
        ),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(
            AddBundleProductToCart::class,
            [
                'cart_id' => '$cart.id$',
                'product_id' => '$bundleProduct.id$',
                'selections' => [['$product.id$']],
                'qty' => 100
            ],
        ),
        DataFixture(QuoteMaskFixture::class, ['cart_id' => '$cart.id$'], 'quoteIdMask'),
    ]
    public function testStockStatusAvailableBundleProduct(): void
    {
        $maskedQuoteId = $this->fixtures->get('quoteIdMask')->getMaskedId();
        $query = $this->getQuery($maskedQuoteId);
        $response = $this->graphQlMutation($query);
        $responseDataObject = new DataObject($response);

        self::assertEquals(
            'IN_STOCK',
            $responseDataObject->getData('cart/itemsV2/items/0/product/stock_status')
        );
    }

    /**
     * Test stock_status for unavailable configurable product
     *
     * @throws Exception
     */
    #[
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(AttributeFixture::class, as: 'attribute'),
        DataFixture(
            ConfigurableProductFixture::class,
            ['_options' => ['$attribute$'], '_links' => ['$product$']],
            'configurable_product'
        ),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(QuoteMaskFixture::class, ['cart_id' => '$cart.id$'], 'quoteIdMask'),
        DataFixture(
            AddConfigurableProductToCartFixture::class,
            [
                'cart_id' => '$cart.id$',
                'product_id' => '$configurable_product.id$',
                'child_product_id' => '$product.id$',
                'qty' => 100
            ],
        ),
        DataFixture(ProductStockFixture::class, ['prod_id' => '$product.id$', 'is_in_stock' => 0], 'prodStock')
    ]
    public function testStockStatusUnavailableConfigurableProduct(): void
    {
        $maskedQuoteId = $this->fixtures->get('quoteIdMask')->getMaskedId();
        $query = $this->getQuery($maskedQuoteId);
        $response = $this->graphQlMutation($query);
        $responseDataObject = new DataObject($response);

        self::assertEquals(
            'OUT_OF_STOCK',
            $responseDataObject->getData('cart/itemsV2/items/0/product/stock_status')
        );
    }

    /**
     * Test stock_status for available configurable product
     *
     * @throws Exception
     */
    #[
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(AttributeFixture::class, as: 'attribute'),
        DataFixture(
            ConfigurableProductFixture::class,
            ['_options' => ['$attribute$'], '_links' => ['$product$']],
            'configurable_product'
        ),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(QuoteMaskFixture::class, ['cart_id' => '$cart.id$'], 'quoteIdMask'),
        DataFixture(ProductStockFixture::class, ['prod_id' => '$product.id$', 'prod_qty' => 100], 'prodStock'),
        DataFixture(
            AddConfigurableProductToCartFixture::class,
            [
                'cart_id' => '$cart.id$',
                'product_id' => '$configurable_product.id$',
                'child_product_id' => '$product.id$',
                'qty' => 90
            ],
        ),
    ]
    public function testStockStatusAvailableConfigurableProduct(): void
    {
        $maskedQuoteId = $this->fixtures->get('quoteIdMask')->getMaskedId();
        $query = $this->getQuery($maskedQuoteId);
        $response = $this->graphQlMutation($query);
        $responseDataObject = new DataObject($response);

        self::assertEquals(
            'IN_STOCK',
            $responseDataObject->getData('cart/itemsV2/items/0/product/stock_status')
        );
    }

    /**
     * Return query with product.stock_status field
     *
     * @param string $cartId
     * @return string
     */
    private function getQuery(string $cartId): string
    {
        return <<<QUERY
{
  cart(cart_id:"{$cartId}") {
    itemsV2 {
      items {
        product {
          stock_status
        }
      }
    }
  }
}
QUERY;
    }
}
