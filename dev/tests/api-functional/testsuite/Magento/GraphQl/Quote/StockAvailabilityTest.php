<?php
/**
 * Copyright 2023 Adobe
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
use Magento\Framework\DataObject;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Test\Fixture\AddProductToCart;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\Quote\Test\Fixture\QuoteIdMask as QuoteMaskFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test discount totals calculation model
 */
class StockAvailabilityTest extends GraphQlAbstract
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

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
        $this->objectManager = Bootstrap::getObjectManager();
        $this->fixtures = DataFixtureStorageManager::getStorage();
        $this->productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
    }

    #[
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
        $response = $this->graphQlMutation($query);
        $responseDataObject = new DataObject($response);

        self::assertFalse(
            $responseDataObject->getData('cart/items/0/is_available')
        );
    }

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

        self::assertTrue(
            $responseDataObject->getData('cart/items/0/is_available')
        );
    }

    #[
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
            $responseDataObject->getData('addProductsToCart/cart/items/0/is_available')
        );
        $response = $this->graphQlMutation($query);
        $responseDataObject = new DataObject($response);
        self::assertFalse(
            $responseDataObject->getData('addProductsToCart/cart/items/0/is_available')
        );
    }

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
        DataFixture(ProductStockFixture::class, ['prod_id' => '$product.id$', 'prod_qty' => 90], 'prodStock')
    ]
    public function testStockStatusUnavailableBundleProduct(): void
    {
        $maskedQuoteId = $this->fixtures->get('quoteIdMask')->getMaskedId();
        $query = $this->getQuery($maskedQuoteId);
        $response = $this->graphQlMutation($query);
        $responseDataObject = new DataObject($response);

        self::assertFalse(
            $responseDataObject->getData('cart/items/0/is_available')
        );
    }

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
            ['sku' => self::PARENT_SKU_BUNDLE, 'price' => 90, '_options' => ['$option$']],
            as:'bundleProduct'
        ),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(
            AddBundleProductToCart::class,
            [
                'cart_id' => '$cart.id$',
                'product_id' => '$bundleProduct.id$',
                'selections' => [['$product.id$']],
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
            $responseDataObject->getData('addProductsToCart/cart/items/0/is_available')
        );

        $response = $this->graphQlMutation($query);
        $responseDataObject = new DataObject($response);

        self::assertFalse(
            $responseDataObject->getData('addProductsToCart/cart/items/0/is_available')
        );
    }

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
        DataFixture(ProductStockFixture::class, ['prod_id' => '$product.id$', 'prod_qty' => 90], 'prodStock')
    ]
    public function testStockStatusUnavailableConfigurableProduct(): void
    {
        $maskedQuoteId = $this->fixtures->get('quoteIdMask')->getMaskedId();
        $query = $this->getQuery($maskedQuoteId);
        $response = $this->graphQlMutation($query);
        $responseDataObject = new DataObject($response);

        self::assertFalse(
            $responseDataObject->getData('cart/items/0/is_available')
        );
    }

    #[
        DataFixture(ProductFixture::class, ['sku' => self::SKU], as: 'product'),
        DataFixture(AttributeFixture::class, as: 'attribute'),
        DataFixture(
            ConfigurableProductFixture::class,
            ['sku' => self::PARENT_SKU_CONFIGURABLE, '_options' => ['$attribute$'], '_links' => ['$product$']],
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
        )
    ]
    public function testStockStatusAddConfigurableProduct(): void
    {
        $maskedQuoteId = $this->fixtures->get('quoteIdMask')->getMaskedId();
        $query = $this->mutationAddConfigurableProduct($maskedQuoteId, self::SKU, self::PARENT_SKU_CONFIGURABLE);
        $response = $this->graphQlMutation($query);
        $responseDataObject = new DataObject($response);
        self::assertTrue(
            $responseDataObject->getData('addProductsToCart/cart/items/1/is_available')
        );
        $response = $this->graphQlMutation($query);
        $responseDataObject = new DataObject($response);
        self::assertFalse(
            $responseDataObject->getData('addProductsToCart/cart/items/0/is_available')
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
  cart(cart_id:"{$cartId}"){
    items{
      is_available
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
      items {
        is_available
      }
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
      items {
        is_available
        product {
          sku
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
        string $parentSku,
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
      parent_sku: "{$parentSku}"
    }]
  ) {
    cart {
      items {
        is_available
        product {
          sku
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
