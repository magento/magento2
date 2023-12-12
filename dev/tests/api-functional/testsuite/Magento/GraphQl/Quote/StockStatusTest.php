<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained from
 * Adobe.
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
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\DataObject;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Test\Fixture\AddProductToCart;
use Magento\Quote\Test\Fixture\CustomerCart;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\Quote\Test\Fixture\QuoteIdMask as QuoteMaskFixture;
use Magento\SalesRule\Test\Fixture\ProductCondition as ProductConditionFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test discount totals calculation model
 */
class StockStatusTest extends GraphQlAbstract
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

        self::assertEquals('unavailable', $responseDataObject->getData('cart/items/0/status'));
    }

    #[
        DataFixture(ProductFixture::class, ['sku' => 'spl-prod', 'price' => 100.00], as: 'product'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(AddProductToCart::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 100]),
        DataFixture(QuoteMaskFixture::class, ['cart_id' => '$cart.id$'], 'quoteIdMask')
    ]
    public function testStockStatusUnavailableAddSimpleProduct(): void
    {
        $sku = 'spl-prod';
        $maskedQuoteId = $this->fixtures->get('quoteIdMask')->getMaskedId();
        $query = $this->mutationAddSimpleProduct($maskedQuoteId, $sku, 100);
        $response = $this->graphQlMutation($query);
        $responseDataObject = new DataObject($response);

        self::assertEquals('unavailable', $responseDataObject->getData('addProductsToCart/cart/items/0/status'));
    }

    #[
        DataFixture(ProductFixture::class, ['price' => 100.00], as: 'product'),
        DataFixture(BundleSelectionFixture::class, ['sku' => '$product.sku$', 'price' => 100, 'price_type' => 0], as:'link'),
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

        self::assertEquals('unavailable', $responseDataObject->getData('cart/items/0/status'));
    }

    #[
        DataFixture(ProductFixture::class, ['price' => 100.00], as: 'product'),
        DataFixture(BundleSelectionFixture::class, ['sku' => '$product.sku$', 'price' => 100, 'price_type' => 0], as:'link'),
        DataFixture(BundleOptionFixture::class, ['title' => 'Checkbox Options', 'type' => 'checkbox',
            'required' => 1,'product_links' => ['$link$']], 'option'),
        DataFixture(
            BundleProductFixture::class,
            ['sku' => 'bundle-prod1', 'price' => 90, '_options' => ['$option$']],
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
        DataFixture(QuoteMaskFixture::class, ['cart_id' => '$cart.id$'], 'quoteIdMask')
    ]
    public function testStockStatusUnavailableAddBundleProduct(): void
    {
        $sku = 'bundle-prod1';
        $product = $this->productRepository->get($sku);

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

        $query = $this->mutationAddBundleProduct($maskedQuoteId, $sku, $bundleOptionIdV2, 100);
        $response = $this->graphQlMutation($query);
        $responseDataObject = new DataObject($response);

        self::assertEquals('unavailable', $responseDataObject->getData('addProductsToCart/cart/items/0/status'));
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

        self::assertEquals('unavailable', $responseDataObject->getData('cart/items/0/status'));
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
      status
    }
  }
}

QUERY;
    }

    private function mutationAddSimpleProduct(string $cartId, string $sku, int $qty): string
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
        status
      }
    }
  }
}
QUERY;
    }

    private function mutationAddBundleProduct(string $cartId, string $sku, string $bundleOptionIdV2, int $qty): string
    {
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
        status
        product {
          sku
        }
      }
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


