<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote\Customer;

use Magento\Bundle\Test\Fixture\AddProductToCart as AddBundleProductToCart;
use Magento\Bundle\Test\Fixture\Link as BundleSelectionFixture;
use Magento\Bundle\Test\Fixture\Option as BundleOptionFixture;
use Magento\Bundle\Test\Fixture\Product as BundleProductFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\Quote\Test\Fixture\CustomerCart;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Integration\Api\CustomerTokenServiceInterface;

/**
 * Test for merging customer carts
 */
class MergeCartsTest extends GraphQlAbstract
{
    /**
     * @var QuoteResource
     */
    private $quoteResource;

    /**
     * @var QuoteFactory
     */
    private $quoteFactory;

    /**
     * @var QuoteIdToMaskedQuoteIdInterface
     */
    private $quoteIdToMaskedId;

    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @var \Magento\Quote\Model\QuoteIdMaskFactory
     */
    private $quoteIdMaskedFactory;

    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\QuoteIdMask
     */
    private $quoteIdMaskedResource;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->quoteResource = $objectManager->get(QuoteResource::class);
        $this->quoteFactory = $objectManager->get(QuoteFactory::class);
        $this->quoteIdToMaskedId = $objectManager->get(QuoteIdToMaskedQuoteIdInterface::class);
        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
        $this->fixtures = DataFixtureStorageManager::getStorage();
        $this->quoteIdMaskedFactory = $objectManager->get(\Magento\Quote\Model\QuoteIdMaskFactory::class);
        $this->quoteIdMaskedResource = $objectManager->get(\Magento\Quote\Model\ResourceModel\Quote\QuoteIdMask::class);
    }

    protected function tearDown(): void
    {
        $quote = $this->quoteFactory->create();
        $this->quoteResource->load($quote, '1', 'customer_id');
        $this->quoteResource->delete($quote);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_virtual_product_saved.php
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     */
    public function testMergeGuestWithCustomerCart()
    {
        $customerQuote = $this->quoteFactory->create();
        $this->quoteResource->load($customerQuote, 'test_quote', 'reserved_order_id');

        $guestQuote = $this->quoteFactory->create();
        $this->quoteResource->load(
            $guestQuote,
            'test_order_with_virtual_product_without_address',
            'reserved_order_id'
        );

        $customerQuoteMaskedId = $this->quoteIdToMaskedId->execute((int)$customerQuote->getId());
        $guestQuoteMaskedId = $this->quoteIdToMaskedId->execute((int)$guestQuote->getId());

        $query = $this->getCartMergeMutation($guestQuoteMaskedId, $customerQuoteMaskedId);
        $mergeResponse = $this->graphQlMutation($query, [], '', $this->getHeaderMap());
        self::assertArrayHasKey('mergeCarts', $mergeResponse);
        $cartResponse = $mergeResponse['mergeCarts'];
        self::assertArrayHasKey('items', $cartResponse);
        self::assertCount(2, $cartResponse['items']);
        $cartResponse = $this->graphQlMutation(
            $this->getCartQuery($customerQuoteMaskedId),
            [],
            '',
            $this->getHeaderMap()
        );

        self::assertArrayHasKey('cart', $cartResponse);
        self::assertArrayHasKey('items', $cartResponse['cart']);
        self::assertCount(2, $cartResponse['cart']['items']);
        $item1 = $cartResponse['cart']['items'][0];
        self::assertArrayHasKey('quantity', $item1);
        self::assertEquals(2, $item1['quantity']);
        $item2 = $cartResponse['cart']['items'][1];
        self::assertArrayHasKey('quantity', $item2);
        self::assertEquals(1, $item2['quantity']);
    }

    #[
        DataFixture(ProductFixture::class, ['sku' => 'simple1', 'price' => 10], as:'p1'),
        DataFixture(ProductFixture::class, ['sku' => 'simple2', 'price' => 20], as:'p2'),
        DataFixture(BundleSelectionFixture::class, ['sku' => '$p1.sku$', 'price' => 10, 'price_type' => 0], as:'link1'),
        DataFixture(BundleSelectionFixture::class, ['sku' => '$p2.sku$', 'price' => 25, 'price_type' => 0], as:'link2'),
        DataFixture(BundleOptionFixture::class, ['title' => 'Checkbox Options', 'type' => 'checkbox',
            'required' => 1,'product_links' => ['$link1$', '$link2$']], 'opt1'),
        DataFixture(BundleOptionFixture::class, ['title' => 'Checkbox Options', 'type' => 'checkbox',
            'required' => 1,'product_links' => ['$link1$', '$link2$']], 'opt2'),
        DataFixture(
            BundleProductFixture::class,
            ['sku' => 'bundle-product-multiselect-checkbox-options','price' => 50,'price_type' => 1,
                '_options' => ['$opt1$', '$opt2$']],
            as:'bp1'
        ),
        DataFixture(Customer::class, ['email' => 'me@example.com'], as: 'customer'),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], as: 'customerCart'),
        DataFixture(
            AddBundleProductToCart::class,
            [
                'cart_id' => '$customerCart.id$',
                'product_id' => '$bp1.id$',
                'selections' => [['$p1.id$'], ['$p2.id$']],
                'qty' => 1
            ]
        ),
        DataFixture(GuestCartFixture::class, as: 'guestCart'),
        DataFixture(
            AddBundleProductToCart::class,
            [
                'cart_id' => '$guestCart.id$',
                'product_id' => '$bp1.id$',
                'selections' => [['$p1.id$'], ['$p2.id$']],
                'qty' => 2
            ]
        )
    ]
    public function testMergeGuestWithCustomerCartBundleProduct()
    {
        $guestCart = $this->fixtures->get('guestCart');
        $guestQuoteMaskedId = $this->quoteIdToMaskedId->execute((int)$guestCart->getId());

        $customerCart = $this->fixtures->get('customerCart');
        $customerCartId = (int)$customerCart->getId();
        $customerQuoteMaskedId = $this->quoteIdToMaskedId->execute($customerCartId);
        if (!$customerQuoteMaskedId) {
            $quoteIdMask = $this->quoteIdMaskedFactory->create()->setQuoteId($customerCartId);
            $this->quoteIdMaskedResource->save($quoteIdMask);
            $customerQuoteMaskedId = $this->quoteIdToMaskedId->execute($customerCartId);
        }

        $queryHeader = $this->getHeaderMap('me@example.com', 'password');
        $cartMergeQuery = $this->getCartMergeMutation($guestQuoteMaskedId, $customerQuoteMaskedId);
        $mergeResponse = $this->graphQlMutation($cartMergeQuery, [], '', $queryHeader);
        self::assertArrayHasKey('mergeCarts', $mergeResponse);

        $cartResponse = $mergeResponse['mergeCarts'];
        self::assertArrayHasKey('items', $cartResponse);
        self::assertCount(1, $cartResponse['items']);
        $cartResponse = $this->graphQlMutation($this->getCartQuery($customerQuoteMaskedId), [], '', $queryHeader);

        self::assertArrayHasKey('cart', $cartResponse);
        self::assertArrayHasKey('items', $cartResponse['cart']);
        self::assertCount(1, $cartResponse['cart']['items']);
        $item1 = $cartResponse['cart']['items'][0];
        self::assertArrayHasKey('quantity', $item1);
        self::assertEquals(3, $item1['quantity']);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_virtual_product_saved.php
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_virtual_product_with_100_qty.php
     */
    public function testMergeGuestWithCustomerCartWithOutOfStockQuantity()
    {
        $customerQuote = $this->quoteFactory->create();
        $this->quoteResource->load($customerQuote, 'test_quote', 'reserved_order_id');

        $guestQuote = $this->quoteFactory->create();
        $this->quoteResource->load(
            $guestQuote,
            'test_order_with_virtual_product_without_address',
            'reserved_order_id'
        );

        $customerQuoteMaskedId = $this->quoteIdToMaskedId->execute((int)$customerQuote->getId());
        $guestQuoteMaskedId = $this->quoteIdToMaskedId->execute((int)$guestQuote->getId());

        $query = $this->getCartMergeMutation($guestQuoteMaskedId, $customerQuoteMaskedId);
        $mergeResponse = $this->graphQlMutation($query, [], '', $this->getHeaderMap());
        self::assertArrayHasKey('mergeCarts', $mergeResponse);
        $cartResponse = $mergeResponse['mergeCarts'];
        self::assertArrayHasKey('items', $cartResponse);
        self::assertCount(1, $cartResponse['items']);
        $cartResponse = $this->graphQlMutation(
            $this->getCartQuery($customerQuoteMaskedId),
            [],
            '',
            $this->getHeaderMap()
        );

        self::assertArrayHasKey('cart', $cartResponse);
        self::assertArrayHasKey('items', $cartResponse['cart']);
        self::assertCount(1, $cartResponse['cart']['items']);
        $item1 = $cartResponse['cart']['items'][0];
        self::assertArrayHasKey('quantity', $item1);
        self::assertEquals(100, $item1['quantity']);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_virtual_product_saved.php
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     */
    public function testGuestCartExpiryAfterMerge()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The cart isn\'t active.');

        $customerQuote = $this->quoteFactory->create();
        $this->quoteResource->load($customerQuote, 'test_quote', 'reserved_order_id');

        $guestQuote = $this->quoteFactory->create();
        $this->quoteResource->load(
            $guestQuote,
            'test_order_with_virtual_product_without_address',
            'reserved_order_id'
        );

        $customerQuoteMaskedId = $this->quoteIdToMaskedId->execute((int)$customerQuote->getId());
        $guestQuoteMaskedId = $this->quoteIdToMaskedId->execute((int)$guestQuote->getId());

        $query = $this->getCartMergeMutation($guestQuoteMaskedId, $customerQuoteMaskedId);
        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
        $this->graphQlMutation(
            $this->getCartQuery($guestQuoteMaskedId),
            [],
            '',
            $this->getHeaderMap()
        );
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/two_customers.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     */
    public function testMergeTwoCustomerCarts()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The current user cannot perform operations on cart');

        $firstQuote = $this->quoteFactory->create();
        $this->quoteResource->load($firstQuote, 'test_quote', 'reserved_order_id');
        $firstMaskedId = $this->quoteIdToMaskedId->execute((int)$firstQuote->getId());

        $createCartResponse = $this->graphQlMutation(
            $this->getCreateEmptyCartMutation(),
            [],
            '',
            $this->getHeaderMap('customer_two@example.com')
        );
        self::assertArrayHasKey('createEmptyCart', $createCartResponse);
        $secondMaskedId = $createCartResponse['createEmptyCart'];
        $this->addSimpleProductToCart($secondMaskedId, $this->getHeaderMap());

        $query = $this->getCartMergeMutation($firstMaskedId, $secondMaskedId);
        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     */
    public function testMergeCartsWithEmptySourceCartId()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Required parameter "source_cart_id" is missing');

        $customerQuote = $this->quoteFactory->create();
        $this->quoteResource->load($customerQuote, 'test_quote', 'reserved_order_id');

        $customerQuoteMaskedId = $this->quoteIdToMaskedId->execute((int)$customerQuote->getId());
        $guestQuoteMaskedId = "";

        $query = $this->getCartMergeMutation($guestQuoteMaskedId, $customerQuoteMaskedId);
        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_virtual_product_saved.php
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testMergeCartsWithEmptyDestinationCartId()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The parameter "destination_cart_id" cannot be empty');

        $guestQuote = $this->quoteFactory->create();
        $this->quoteResource->load(
            $guestQuote,
            'test_order_with_virtual_product_without_address',
            'reserved_order_id'
        );

        $customerQuoteMaskedId = "";
        $guestQuoteMaskedId = $this->quoteIdToMaskedId->execute((int)$guestQuote->getId());

        $query = $this->getCartMergeMutation($guestQuoteMaskedId, $customerQuoteMaskedId);
        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_virtual_product_saved.php
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     */
    public function testMergeCartsWithoutDestinationCartId()
    {
        $guestQuote = $this->quoteFactory->create();
        $this->quoteResource->load(
            $guestQuote,
            'test_order_with_virtual_product_without_address',
            'reserved_order_id'
        );
        $guestQuoteMaskedId = $this->quoteIdToMaskedId->execute((int)$guestQuote->getId());
        $query = $this->getCartMergeMutationWithoutDestinationCartId(
            $guestQuoteMaskedId
        );
        $mergeResponse = $this->graphQlMutation($query, [], '', $this->getHeaderMap());

        self::assertArrayHasKey('mergeCarts', $mergeResponse);
        $cartResponse = $mergeResponse['mergeCarts'];
        self::assertArrayHasKey('items', $cartResponse);
        self::assertCount(2, $cartResponse['items']);

        $customerQuote = $this->quoteFactory->create();
        $this->quoteResource->load($customerQuote, 'test_quote', 'reserved_order_id');
        $customerQuoteMaskedId = $this->quoteIdToMaskedId->execute((int)$customerQuote->getId());

        $cartResponse = $this->graphQlMutation(
            $this->getCartQuery($customerQuoteMaskedId),
            [],
            '',
            $this->getHeaderMap()
        );

        self::assertArrayHasKey('cart', $cartResponse);
        self::assertArrayHasKey('items', $cartResponse['cart']);
        self::assertCount(2, $cartResponse['cart']['items']);
        $item1 = $cartResponse['cart']['items'][0];
        self::assertArrayHasKey('quantity', $item1);
        self::assertEquals(2, $item1['quantity']);
        $item2 = $cartResponse['cart']['items'][1];
        self::assertArrayHasKey('quantity', $item2);
        self::assertEquals(1, $item2['quantity']);
    }

    /**
     * Add simple product to cart
     *
     * @param string $maskedId
     * @param array $headerMap
     * @throws \Exception
     */
    private function addSimpleProductToCart(string $maskedId, array $headerMap): void
    {
        $result = $this->graphQlMutation($this->getAddProductToCartMutation($maskedId), [], '', $headerMap);
        self::assertArrayHasKey('addSimpleProductsToCart', $result);
        self::assertArrayHasKey('cart', $result['addSimpleProductsToCart']);
        self::assertArrayHasKey('items', $result['addSimpleProductsToCart']['cart']);
        self::assertArrayHasKey(0, $result['addSimpleProductsToCart']['cart']['items']);
        self::assertArrayHasKey('quantity', $result['addSimpleProductsToCart']['cart']['items'][0]);
        self::assertEquals(1, $result['addSimpleProductsToCart']['cart']['items'][0]['quantity']);
        self::assertArrayHasKey('product', $result['addSimpleProductsToCart']['cart']['items'][0]);
        self::assertArrayHasKey('sku', $result['addSimpleProductsToCart']['cart']['items'][0]['product']);
        self::assertEquals('simple_product', $result['addSimpleProductsToCart']['cart']['items'][0]['product']['sku']);
    }

    /**
     * Create the mergeCart mutation
     *
     * @param string $guestQuoteMaskedId
     * @param string $customerQuoteMaskedId
     * @return string
     */
    private function getCartMergeMutation(string $guestQuoteMaskedId, string $customerQuoteMaskedId): string
    {
        return <<<QUERY
mutation {
  mergeCarts(
    source_cart_id: "{$guestQuoteMaskedId}"
    destination_cart_id: "{$customerQuoteMaskedId}"
  ){
  items {
      quantity
      product {
        sku
      }
    }
  }
}
QUERY;
    }

    /**
     * Create the mergeCart mutation
     *
     * @param string $guestQuoteMaskedId
     * @return string
     */
    private function getCartMergeMutationWithoutDestinationCartId(
        string $guestQuoteMaskedId
    ): string {
        return <<<QUERY
mutation {
  mergeCarts(
    source_cart_id: "{$guestQuoteMaskedId}"
  ){
  items {
      quantity
      product {
        sku
      }
    }
  }
}
QUERY;
    }

    /**
     * Get cart query
     *
     * @param string $maskedId
     * @return string
     */
    private function getCartQuery(string $maskedId): string
    {
        return <<<QUERY
{
  cart(cart_id: "{$maskedId}") {
    items {
      quantity
      product {
        sku
      }
    }
  }
}
QUERY;
    }

    /**
     * Get create empty cart mutation
     *
     * @return string
     */
    private function getCreateEmptyCartMutation(): string
    {
        return <<<QUERY
mutation {
  createEmptyCart
}
QUERY;
    }

    /**
     * Get add product to cart mutation
     *
     * @param string $maskedId
     * @return string
     */
    private function getAddProductToCartMutation(string $maskedId): string
    {
        return <<<QUERY
mutation {
  addSimpleProductsToCart(input: {
    cart_id: "{$maskedId}"
    cart_items: {
      data: {
        quantity: 1
        sku: "simple_product"
      }
    }
  }) {
    cart {
      items {
        quantity
        product {
          sku
        }
      }
    }
  }
}
QUERY;
    }

    /**
     * @param string $username
     * @param string $password
     * @return array
     * @throws \Magento\Framework\Exception\AuthenticationException
     */
    private function getHeaderMap(string $username = 'customer@example.com', string $password = 'password'): array
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($username, $password);
        $headerMap = ['Authorization' => 'Bearer ' . $customerToken];
        return $headerMap;
    }
}
