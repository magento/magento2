<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\CatalogInventory;

use Magento\Bundle\Test\Fixture\AddProductToCart as AddBundleProductToCart;
use Magento\Bundle\Test\Fixture\Link as BundleSelectionFixture;
use Magento\Bundle\Test\Fixture\Option as BundleOptionFixture;
use Magento\Bundle\Test\Fixture\Product as BundleProductFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Catalog\Test\Fixture\ProductStock as ProductStockFixture;
use Magento\Checkout\Test\Fixture\PlaceOrder as PlaceOrderFixture;
use Magento\Checkout\Test\Fixture\SetBillingAddress as SetBillingAddressFixture;
use Magento\Checkout\Test\Fixture\SetDeliveryMethod as SetDeliveryMethodFixture;
use Magento\Checkout\Test\Fixture\SetGuestEmail as SetGuestEmailFixture;
use Magento\Checkout\Test\Fixture\SetPaymentMethod as SetPaymentMethodFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress as SetShippingAddressFixture;
use Magento\ConfigurableProduct\Test\Fixture\AddProductToCart as AddConfigurableProductToCartFixture;
use Magento\ConfigurableProduct\Test\Fixture\Attribute as AttributeFixture;
use Magento\ConfigurableProduct\Test\Fixture\Product as ConfigurableProductFixture;
use Magento\Framework\DataObject;
use Magento\Quote\Test\Fixture\AddProductToCart;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\Quote\Test\Fixture\QuoteIdMask as QuoteMaskFixture;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Product quantity test model
 */
class StockQuantityTest extends GraphQlAbstract
{
    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->fixtures = DataFixtureStorageManager::getStorage();
    }

    #[
        Config('cataloginventory/options/not_available_message', 1),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(AddProductToCart::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
        DataFixture(QuoteMaskFixture::class, ['cart_id' => '$cart.id$'], 'quoteIdMask'),
        DataFixture(ProductStockFixture::class, ['prod_id' => '$product.id$', 'prod_qty' => 10])
    ]
    public function testStockQuantitySimpleProduct(): void
    {
        $this->assertProductStockQuantity(10);
    }

    #[
        Config('cataloginventory/options/not_available_message', 1),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(
            BundleSelectionFixture::class,
            ['sku' => '$product.sku$'],
            'link'
        ),
        DataFixture(
            BundleOptionFixture::class,
            [
                'title' => 'Checkbox Options',
                'type' => 'checkbox',
                'required' => 1,
                'product_links' => ['$link$']
            ],
            'option'
        ),
        DataFixture(
            BundleProductFixture::class,
            ['_options' => ['$option$']],
            'bundleProduct'
        ),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(
            AddBundleProductToCart::class,
            [
                'cart_id' => '$cart.id$',
                'product_id' => '$bundleProduct.id$',
                'selections' => [['$product.id$']],
            ],
        ),
        DataFixture(QuoteMaskFixture::class, ['cart_id' => '$cart.id$'], 'quoteIdMask'),
        DataFixture(ProductStockFixture::class, ['prod_id' => '$product.id$', 'prod_qty' => 10])
    ]
    public function testStockQuantityBundleProduct(): void
    {
        $this->assertProductStockQuantity(10);
        $this->assertNoStockQuantity('bundleProduct');
    }

    #[
        Config('cataloginventory/options/not_available_message', 1),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(AttributeFixture::class, as: 'attribute'),
        DataFixture(
            ConfigurableProductFixture::class,
            [
                '_options' => ['$attribute$'],
                '_links' => ['$product$']
            ],
            'configurableProduct'
        ),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(QuoteMaskFixture::class, ['cart_id' => '$cart.id$'], 'quoteIdMask'),
        DataFixture(
            AddConfigurableProductToCartFixture::class,
            [
                'cart_id' => '$cart.id$',
                'product_id' => '$configurableProduct.id$',
                'child_product_id' => '$product.id$',
            ],
        ),
        DataFixture(ProductStockFixture::class, ['prod_id' => '$product.id$', 'prod_qty' => 10]),
    ]
    public function testStockQuantityConfigurableProduct(): void
    {
        $this->assertProductStockQuantity(10);
        $this->assertNoStockQuantity('configurableProduct');
    }

    #[
        Config('cataloginventory/options/not_available_message', 2),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(AddProductToCart::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
        DataFixture(QuoteMaskFixture::class, ['cart_id' => '$cart.id$'], 'quoteIdMask'),
    ]
    public function testStockQuantityEmpty(): void
    {
        $this->assertProductStockQuantity(null);
    }

    #[
        Config('cataloginventory/options/not_available_message', 1),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(ProductStockFixture::class, ['prod_id' => '$product.id$', 'prod_qty' => 10]),
        DataFixture(AddProductToCart::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 2]),
        DataFixture(QuoteMaskFixture::class, ['cart_id' => '$cart.id$'], 'quoteIdMask'),
        DataFixture(ProductStockFixture::class, ['prod_id' => '$product.id$', 'prod_qty' => 8])
    ]
    public function testSaleableQuantitySimpleProductAfterStockUpdate(): void
    {
        $this->assertProductStockQuantity(8);
    }

    #[
        Config('cataloginventory/options/not_available_message', 1),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(ProductStockFixture::class, ['prod_id' => '$product.id$', 'prod_qty' => 10]),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(AddProductToCart::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 1]),
        DataFixture(QuoteMaskFixture::class, ['cart_id' => '$cart.id$'], 'quoteIdMask'),
        DataFixture(GuestCartFixture::class, as: 'cart2'),
        DataFixture(AddProductToCart::class, ['cart_id' => '$cart2.id$', 'product_id' => '$product.id$', 'qty' => 5]),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart2.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart2.id$']),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart2.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart2.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart2.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart2.id$'], 'order')
    ]
    public function testSaleableQuantitySimpleProductAfterPlaceOrder(): void
    {
        $this->assertProductStockQuantity(5);
    }

    /**
     * Asserts products stock quantity from cart & product query
     *
     * @param float|null $stockQuantity
     * @return void
     */
    private function assertProductStockQuantity(?float $stockQuantity): void
    {
        $maskedQuoteId = $this->fixtures->get('quoteIdMask')->getMaskedId();
        $cartQuery = $this->getCartQuery($maskedQuoteId);
        $cartResponse = $this->graphQlMutation($cartQuery);
        $cartResponseDataObject = new DataObject($cartResponse);
        self::assertEquals(
            $stockQuantity,
            $cartResponseDataObject->getData('cart/itemsV2/items/0/product/quantity')
        );

        $productQuery = $this->getProductQuery($this->fixtures->get('product')->getSku());
        $productResponse = $this->graphQlMutation($productQuery);
        $productResponseDataObject = new DataObject($productResponse);
        self::assertEquals(
            $stockQuantity,
            $productResponseDataObject->getData('products/items/0/quantity')
        );
    }

    /**
     * Asserts bundle & conf product stock quantity from product query
     *
     * @param string $productFixture
     * @return void
     */
    private function assertNoStockQuantity(string $productFixture): void
    {
        $productQuery = $this->getProductQuery($this->fixtures->get($productFixture)->getSku());
        $productResponse = $this->graphQlMutation($productQuery);
        $productResponseDataObject = new DataObject($productResponse);
        self::assertEquals(
            0,
            $productResponseDataObject->getData('products/items/0/quantity')
        );
    }

    /**
     * Return cart query with product.quantity field
     *
     * @param string $cartId
     * @return string
     */
    private function getCartQuery(string $cartId): string
    {
        return <<<QUERY
{
  cart(cart_id:"{$cartId}") {
    itemsV2 {
      items {
        product {
          quantity
        }
      }
    }
  }
}
QUERY;
    }

    /**
     * Return product query with product.quantity field
     *
     * @param string $sku
     * @return string
     */
    private function getProductQuery(string $sku): string
    {
        return <<<QUERY
{
  products(filter: { sku: { eq: "{$sku}" } }) {
    items {
      quantity
    }
  }
}
QUERY;
    }
}
