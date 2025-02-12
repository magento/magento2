<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote;

use Magento\Bundle\Test\Fixture\AddProductToCart as AddBundleProductToCart;
use Magento\Bundle\Test\Fixture\Link as BundleSelectionFixture;
use Magento\Bundle\Test\Fixture\Option as BundleOptionFixture;
use Magento\Bundle\Test\Fixture\Product as BundleProductFixture;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Catalog\Test\Fixture\ProductStock as ProductStockFixture;
use Magento\ConfigurableProduct\Test\Fixture\AddProductToCart as AddConfigurableProductToCartFixture;
use Magento\ConfigurableProduct\Test\Fixture\Attribute as AttributeFixture;
use Magento\ConfigurableProduct\Test\Fixture\Product as ConfigurableProductFixture;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Framework\DataObject;
use Magento\Quote\Test\Fixture\AddProductToCart;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\Quote\Test\Fixture\QuoteIdMask as QuoteMaskFixture;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Stock Availability [not_available_message] Test model
 */
class StockAvailabilityTest extends GraphQlAbstract
{
    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @var ProductRepositoryInterface|mixed
     */
    private $productRepository;

    private const SKU = 'simple_10';
    private const PARENT_SKU_BUNDLE = 'parent_bundle';
    private const PARENT_SKU_CONFIGURABLE = 'parent_configurable';

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->fixtures = DataFixtureStorageManager::getStorage();
        $this->productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
    }

    #[
        Config('cataloginventory/options/not_available_message', 0),
        DataFixture(ProductFixture::class, ['price' => 100.00], as: 'product'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(AddProductToCart::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 100]),
        DataFixture(QuoteMaskFixture::class, ['cart_id' => '$cart.id$'], 'quoteIdMask'),
        DataFixture(ProductStockFixture::class, ['prod_id' => '$product.id$', 'prod_qty' => 90], 'prodStock')
    ]
    public function testStockStatusUnavailableSimpleProduct(): void
    {
        $maskedQuoteId = $this->fixtures->get('quoteIdMask')->getMaskedId();
        $query = $this->getQuery($maskedQuoteId);
        $response = $this->graphQlQuery($query);
        $responseDataObject = new DataObject($response);

        self::assertFalse(
            $responseDataObject->getData('cart/itemsV2/items/0/is_available')
        );
        self::assertEquals(
            'Not enough items for sale',
            $responseDataObject->getData('cart/itemsV2/items/0/not_available_message')
        );
    }

    #[
        Config('cataloginventory/options/not_available_message', 1),
        Config('cataloginventory/options/stock_threshold_qty', 100),
        DataFixture(ProductFixture::class, ['price' => 100.00], as: 'product'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(AddProductToCart::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 100]),
        DataFixture(QuoteMaskFixture::class, ['cart_id' => '$cart.id$'], 'quoteIdMask')
    ]
    public function testStockStatusAvailableSimpleProduct(): void
    {
        $maskedQuoteId = $this->fixtures->get('quoteIdMask')->getMaskedId();
        $query = $this->getQuery($maskedQuoteId);
        $response = $this->graphQlQuery($query);
        $responseDataObject = new DataObject($response);

        self::assertTrue(
            $responseDataObject->getData('cart/itemsV2/items/0/is_available')
        );
        self::assertNull(
            $responseDataObject->getData('cart/itemsV2/items/0/not_available_message')
        );
    }

    #[
        Config('cataloginventory/options/not_available_message', 1),
        Config('cataloginventory/options/stock_threshold_qty', 100),
        DataFixture(ProductFixture::class, ['price' => 100.00], as: 'product'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(AddProductToCart::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 20]),
        DataFixture(QuoteMaskFixture::class, ['cart_id' => '$cart.id$'], 'quoteIdMask'),
        DataFixture(ProductStockFixture::class, ['prod_id' => '$product.id$', 'prod_qty' => 10], 'prodStock')
    ]
    public function testStockStatusUnavailableSimpleProductOption1(): void
    {
        $maskedQuoteId = $this->fixtures->get('quoteIdMask')->getMaskedId();
        $query = $this->getQuery($maskedQuoteId);
        $response = $this->graphQlQuery($query);
        $responseDataObject = new DataObject($response);

        self::assertFalse(
            $responseDataObject->getData('cart/itemsV2/items/0/is_available')
        );
        self::assertEquals(10, $responseDataObject->getData('cart/itemsV2/items/0/product/only_x_left_in_stock'));
        self::assertEquals(
            'Only 10 of 20 available',
            $responseDataObject->getData('cart/itemsV2/items/0/not_available_message')
        );
    }

    #[
        Config('cataloginventory/options/not_available_message', 1),
        Config('cataloginventory/options/stock_threshold_qty', 100),
        DataFixture(ProductFixture::class, ['sku' => self::SKU, 'price' => 100.00], as: 'product'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(AddProductToCart::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 99]),
        DataFixture(QuoteMaskFixture::class, ['cart_id' => '$cart.id$'], 'quoteIdMask')
    ]
    public function testStockStatusAddSimpleProduct(): void
    {
        $maskedQuoteId = $this->fixtures->get('quoteIdMask')->getMaskedId();
        $query = $this->mutationAddSimpleProduct($maskedQuoteId, self::SKU, 1);
        $response = $this->graphQlMutation($query);
        $responseDataObject = new DataObject($response);

        self::assertTrue(
            $responseDataObject->getData('addProductsToCart/cart/itemsV2/items/0/is_available')
        );
        self::assertNull(
            $responseDataObject->getData('addProductsToCart/cart/itemsV2/items/0/not_available_message')
        );
    }

    #[
        Config('cataloginventory/options/not_available_message', 0),
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
        DataFixture(ProductStockFixture::class, ['prod_id' => '$product.id$', 'prod_qty' => 90], 'prodStock')
    ]
    public function testStockStatusUnavailableBundleProduct(): void
    {
        $maskedQuoteId = $this->fixtures->get('quoteIdMask')->getMaskedId();
        $query = $this->getQuery($maskedQuoteId);
        $response = $this->graphQlQuery($query);
        $responseDataObject = new DataObject($response);

        self::assertFalse(
            $responseDataObject->getData('cart/itemsV2/items/0/is_available')
        );
        self::assertEquals(
            'Not enough items for sale',
            $responseDataObject->getData('cart/itemsV2/items/0/not_available_message')
        );
    }

    #[
        Config('cataloginventory/options/not_available_message', 1),
        Config('cataloginventory/options/stock_threshold_qty', 100),
        DataFixture(ProductFixture::class, ['price' => 100.00], as: 'product'),
        DataFixture(ProductStockFixture::class, ['prod_id' => '$product.id$', 'prod_qty' => 100], 'prodStock'),
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
        DataFixture(ProductStockFixture::class, ['prod_id' => '$bundleProduct.id$', 'prod_qty' => 100], 'prodStock'),
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
        DataFixture(ProductStockFixture::class, ['prod_id' => '$product.id$', 'prod_qty' => 90], 'prodStock')
    ]
    public function testStockStatusUnavailableBundleProductOption1(): void
    {
        $maskedQuoteId = $this->fixtures->get('quoteIdMask')->getMaskedId();
        $query = $this->getQuery($maskedQuoteId);
        $response = $this->graphQlQuery($query);
        $responseDataObject = new DataObject($response);

        self::assertFalse(
            $responseDataObject->getData('cart/itemsV2/items/0/is_available')
        );
        self::assertEquals(
            'Only 90 of 100 available',
            $responseDataObject->getData('cart/itemsV2/items/0/not_available_message')
        );
    }

    #[
        Config('cataloginventory/options/not_available_message', 1),
        Config('cataloginventory/options/stock_threshold_qty', 100),
        DataFixture(ProductFixture::class, ['price' => 100.00], as: 'product'),
        DataFixture(ProductStockFixture::class, ['prod_id' => '$product.id$', 'prod_qty' => 100], 'prodStock'),
        DataFixture(
            BundleSelectionFixture::class,
            [
                'sku' => '$product.sku$', 'price' => 100, 'price_type' => 0
            ],
            as:'link'
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
            [
                'sku' => self::PARENT_SKU_BUNDLE,
                'price' => 90,
                '_options' => ['$option$']
            ],
            as:'bundleProduct'
        ),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(
            AddBundleProductToCart::class,
            [
                'cart_id' => '$cart.id$',
                'product_id' => '$bundleProduct.id$',
                'selections' => [
                    ['$product.id$']
                ],
                'qty' => 99
            ],
        ),
        DataFixture(QuoteMaskFixture::class, ['cart_id' => '$cart.id$'], 'quoteIdMask')
    ]
    public function testStockStatusAddBundleProduct(): void
    {
        $product = $this->productRepository->get(self::PARENT_SKU_BUNDLE);

        /** @var $typeInstance \Magento\Bundle\Model\Product\Type */
        $typeInstance = $product->getTypeInstance();
        $typeInstance->setStoreFilter($product->getStoreId(), $product);
        /** @var $option \Magento\Bundle\Model\Option */
        $option = $typeInstance->getOptionsCollection($product)->getFirstItem();
        /** @var \Magento\Catalog\Model\Product $selection */
        $selection = $typeInstance->getSelectionsCollection([$option->getId()], $product)->getFirstItem();
        $optionId = $option->getId();
        $selectionId = $selection->getSelectionId();

        $bundleOptionIdV2 = $this->generateBundleOptionIdV2((int) $optionId, (int) $selectionId, 1);
        $maskedQuoteId = $this->fixtures->get('quoteIdMask')->getMaskedId();

        $query = $this->mutationAddBundleProduct($maskedQuoteId, self::PARENT_SKU_BUNDLE, $bundleOptionIdV2);
        $response = $this->graphQlMutation($query);
        $responseDataObject = new DataObject($response);

        self::assertTrue(
            $responseDataObject->getData('addProductsToCart/cart/itemsV2/items/0/is_available')
        );
        self::assertNull(
            $responseDataObject->getData('addProductsToCart/cart/itemsV2/items/0/not_available_message')
        );
    }

    #[
        Config('cataloginventory/options/not_available_message', 0),
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
        DataFixture(ProductStockFixture::class, ['prod_id' => '$product.id$', 'prod_qty' => 90], 'prodStock')
    ]
    public function testStockStatusUnavailableConfigurableProduct(): void
    {
        $maskedQuoteId = $this->fixtures->get('quoteIdMask')->getMaskedId();
        $query = $this->getQuery($maskedQuoteId);
        $response = $this->graphQlQuery($query);
        $responseDataObject = new DataObject($response);

        self::assertFalse(
            $responseDataObject->getData('cart/itemsV2/items/0/is_available')
        );
        self::assertEquals(
            'Not enough items for sale',
            $responseDataObject->getData('cart/itemsV2/items/0/not_available_message')
        );
    }

    #[
        Config('cataloginventory/options/not_available_message', 1),
        Config('cataloginventory/options/stock_threshold_qty', 100),
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
        DataFixture(ProductStockFixture::class, ['prod_id' => '$product.id$', 'prod_qty' => 90], 'prodStock')
    ]
    public function testStockStatusUnavailableConfigurableProductOption1(): void
    {
        $maskedQuoteId = $this->fixtures->get('quoteIdMask')->getMaskedId();
        $query = $this->getQuery($maskedQuoteId);
        $response = $this->graphQlQuery($query);
        $responseDataObject = new DataObject($response);

        self::assertFalse(
            $responseDataObject->getData('cart/itemsV2/items/0/is_available')
        );

        self::assertEquals(90, $responseDataObject->getData('cart/itemsV2/items/0/product/only_x_left_in_stock'));

        self::assertEquals(
            'Only 90 of 100 available',
            $responseDataObject->getData('cart/itemsV2/items/0/not_available_message')
        );
    }

    #[
        Config('cataloginventory/options/not_available_message', 1),
        Config('cataloginventory/options/stock_threshold_qty', 100),
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
        $response = $this->graphQlQuery($query);
        $responseDataObject = new DataObject($response);

        self::assertTrue(
            $responseDataObject->getData('cart/itemsV2/items/0/is_available')
        );

        self::assertNull(
            $responseDataObject->getData('cart/itemsV2/items/0/not_available_message')
        );
    }

    #[
        Config('cataloginventory/options/not_available_message', 1),
        Config('cataloginventory/options/stock_threshold_qty', 100),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'product_variant_1',
            ],
            'product_variant_1'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'product_variant_2',
            ],
            'product_variant_2'
        ),
        DataFixture(AttributeFixture::class, as: 'attribute'),
        DataFixture(
            ConfigurableProductFixture::class,
            [
                'type_id' => 'simple',
                'sku' => self::PARENT_SKU_CONFIGURABLE,
                '_options' => [
                    '$attribute$'
                ],
                '_links' => [
                    '$product_variant_1$',
                    '$product_variant_2$',
                ],
            ],
            'configurable_product'
        ),
        DataFixture(
            ProductStockFixture::class,
            [
                'prod_id' => 'product_variant_1.id$',
                'prod_qty' => 100
            ],
            'productVariantStock1'
        ),
        DataFixture(
            ProductStockFixture::class,
            [
                'prod_id' => 'product_variant_2.id$',
                'prod_qty' => 100
            ],
            'productVariantStock2'
        ),

        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(QuoteMaskFixture::class, ['cart_id' => '$cart.id$'], 'quoteIdMask'),
    ]
    public function testStockStatusAddConfigurableProduct(): void
    {
        $maskedQuoteId = $this->fixtures->get('quoteIdMask')->getMaskedId();
        $productVariant1 =  $this->fixtures->get('product_variant_1');
        /** @var AttributeInterface $attribute */
        $attribute = $this->fixtures->get('attribute');
        /** @var AttributeOptionInterface $option */
        $option = $attribute->getOptions()[1];
        $selectedOption = base64_encode("configurable/{$attribute->getAttributeId()}/{$option->getValue()}");
        $query = $this->mutationAddConfigurableProduct(
            $maskedQuoteId,
            $productVariant1->getData('sku'),
            $selectedOption,
            100
        );

        $response = $this->graphQlMutation($query);
        $responseDataObject = new DataObject($response);

        self::assertTrue(
            $responseDataObject->getData('addProductsToCart/cart/itemsV2/items/0/is_available')
        );

        self::assertNull(
            $responseDataObject->getData('addProductsToCart/cart/itemsV2/items/0/not_available_message')
        );
    }

    #[
        Config('cataloginventory/options/not_available_message', 1),
        Config('cataloginventory/options/stock_threshold_qty', 100),
        DataFixture(ProductFixture::class, ['price' => 100.00], as: 'product'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(AddProductToCart::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 100]),
        DataFixture(QuoteMaskFixture::class, ['cart_id' => '$cart.id$'], 'quoteIdMask'),
        DataFixture(ProductStockFixture::class, ['prod_id' => '$product.id$', 'prod_qty' => 90], 'prodStock')
    ]
    public function testNotAvailableMessageOption1(): void
    {
        $maskedQuoteId = $this->fixtures->get('quoteIdMask')->getMaskedId();
        $query = $this->getQuery($maskedQuoteId);
        $response = $this->graphQlQuery($query);
        $responseDataObject = new DataObject($response);

        self::assertFalse(
            $responseDataObject->getData('cart/itemsV2/items/0/is_available')
        );

        self::assertEquals(90, $responseDataObject->getData('cart/itemsV2/items/0/product/only_x_left_in_stock'));

        self::assertEquals(
            'Only 90 of 100 available',
            $responseDataObject->getData('cart/itemsV2/items/0/not_available_message')
        );
    }

    /**
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
        is_available
        not_available_message
        product {
            sku
            only_x_left_in_stock
        }
      }
    }
  }
}
QUERY;
    }

    private function mutationAddSimpleProduct(string $cartId, string $sku, int $qty = 1): string
    {
        return <<<QUERY
mutation {
  addProductsToCart(
    cartId: "{$cartId}",
    cartItems: [
    {
      sku: "{$sku}"
      quantity: $qty
    }]
  ) {
    cart {
      itemsV2 {
        items {
          is_available
          not_available_message
        }
      }
    }
    user_errors {
      code
      message
    }
  }
}
QUERY;
    }

    private function mutationAddBundleProduct(
        string $cartId,
        string $sku,
        string $bundleOptionIdV2,
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
        "{$bundleOptionIdV2}"
      ]
    }]
  ) {
    cart {
      itemsV2 {
        items {
          is_available
          not_available_message
          product {
            sku
          }
        }
      }
    }
  }
}
QUERY;
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
    itemsV2 {
      items {
        quantity
        is_available
        not_available_message
        product {
          sku
          only_x_left_in_stock
        }
      }
      }
    }
    user_errors {
      code
      message
    }
  }
}
QUERY;
    }

    private function generateBundleOptionIdV2(int $optionId, int $selectionId, int $quantity): string
    {
        return base64_encode("bundle/$optionId/$selectionId/$quantity");
    }
}
