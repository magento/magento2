<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Api;

use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteIdMask;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Class for payment info in quote for registered customer.
 */
class CartAddingItemsTest extends WebapiAbstract
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var ProductResource
     */
    private $productResource;

    /**
     * @var array
     */
    private $createdQuotes = [];

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->productResource = $this->objectManager->get(ProductResource::class);
    }

    protected function tearDown(): void
    {
        /** @var Quote $quote */
        $quote = $this->objectManager->create(Quote::class);
        foreach ($this->createdQuotes as $quoteId) {
            $quote->load($quoteId);
            $quote->delete();
        }
    }

    /**
     * Test qty for cart after adding grouped product qty specified only for goruped product.
     *
     * @magentoApiDataFixture Magento/GroupedProduct/_files/product_grouped_with_simple.php
     * @magentoApiDataFixture Magento/Customer/_files/customer_one_address.php
     * @return void
     */
    public function testAddToCartGroupedWithParentQuantity(): void
    {
        $this->_markTestAsRestOnly();

        // Get customer ID token
        /** @var CustomerTokenServiceInterface $customerTokenService */
        $customerTokenService = $this->objectManager->create(CustomerTokenServiceInterface::class);
        $token = $customerTokenService->createCustomerAccessToken(
            'customer_one_address@test.com',
            'password'
        );

        // Creating empty cart for registered customer.
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/carts/mine',
                'httpMethod' => Request::HTTP_METHOD_POST,
                'token' => $token
            ]
        ];

        $quoteId = $this->_webApiCall($serviceInfo, ['customerId' => 999]); // customerId 999 will get overridden
        $this->assertGreaterThan(0, $quoteId);

        /** @var CartRepositoryInterface $cartRepository */
        $cartRepository = $this->objectManager->get(CartRepositoryInterface::class);
        $quote = $cartRepository->get($quoteId);

        $quoteItems = $quote->getItemsCollection();
        foreach ($quoteItems as $item) {
            $quote->removeItem($item->getId())->save();
        }

        $requestData = [
            'cartItem' => [
                'quote_id' => $quoteId,
                'sku' => 'grouped',
                'qty' => 7
            ]
        ];
        $this->_webApiCall($this->getServiceInfoAddToCart($token), $requestData);

        foreach ($quote->getAllItems() as $item) {
            $this->assertEquals(7, $item->getQty());
        }
        $this->createdQuotes[] = $quoteId;
    }

    /**
     * Test price for cart after adding product to.
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_without_options_with_stock_data.php
     * @magentoApiDataFixture Magento/Customer/_files/customer_one_address.php
     * @return void
     */
    public function testPriceForCreatingQuoteFromEmptyCart()
    {
        $this->_markTestAsRestOnly();

        // Get customer ID token
        /** @var CustomerTokenServiceInterface $customerTokenService */
        $customerTokenService = $this->objectManager->create(
            CustomerTokenServiceInterface::class
        );
        $token = $customerTokenService->createCustomerAccessToken(
            'customer_one_address@test.com',
            'password'
        );

        // Creating empty cart for registered customer.
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/carts/mine',
                'httpMethod' => Request::HTTP_METHOD_POST,
                'token' => $token
            ]
        ];

        $quoteId = $this->_webApiCall($serviceInfo, ['customerId' => 999]); // customerId 999 will get overridden
        $this->assertGreaterThan(0, $quoteId);

        // Adding item to the cart
        $requestData = [
            'cartItem' => [
                'quote_id' => $quoteId,
                'sku' => 'simple',
                'qty' => 1
            ]
        ];
        $item = $this->_webApiCall($this->getServiceInfoAddToCart($token), $requestData);
        $this->assertNotEmpty($item);
        $this->assertEquals(10, $item['price']);

        // Get payment information
        $serviceInfoForGettingPaymentInfo = [
            'rest' => [
                'resourcePath' => '/V1/carts/mine/payment-information',
                'httpMethod' => Request::HTTP_METHOD_GET,
                'token' => $token
            ]
        ];
        $paymentInfo = $this->_webApiCall($serviceInfoForGettingPaymentInfo);
        $this->assertEquals($paymentInfo['totals']['grand_total'], 10);

        $this->createdQuotes[] = $quoteId;
//        /** @var \Magento\Quote\Model\Quote $quote */
//        $quote = $this->objectManager->create(\Magento\Quote\Model\Quote::class);
//        $quote->load($quoteId);
//        $quote->delete();
    }

    /**
     * Test qty for cart after adding grouped product with custom qty.
     *
     * @magentoApiDataFixture Magento/GroupedProduct/_files/product_grouped_with_simple.php
     * @magentoApiDataFixture Magento/Customer/_files/customer_one_address.php
     * @return void
     */
    public function testAddToCartGroupedCustomQuantity(): void
    {
        $this->_markTestAsRestOnly();

        $firstProductId = $this->productResource->getIdBySku('simple_11');
        $secondProductId = $this->productResource->getIdBySku('simple_22');
        $qtyData = [$firstProductId => 2, $secondProductId => 4];

        // Get customer ID token
        /** @var CustomerTokenServiceInterface $customerTokenService */
        $customerTokenService = $this->objectManager->create(CustomerTokenServiceInterface::class);
        $token = $customerTokenService->createCustomerAccessToken(
            'customer_one_address@test.com',
            'password'
        );

        // Creating empty cart for registered customer.
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/carts/mine',
                'httpMethod' => Request::HTTP_METHOD_POST,
                'token' => $token
            ]
        ];

        $quoteId = $this->_webApiCall($serviceInfo, ['customerId' => 999]); // customerId 999 will get overridden
        $this->assertGreaterThan(0, $quoteId);

        // Adding item to the cart
        $productOptionData = [
            'extension_attributes' => [
                'grouped_options' => [
                    ['id' => $firstProductId, 'qty' => $qtyData[$firstProductId]],
                    ['id' => $secondProductId, 'qty' => $qtyData[$secondProductId]],
                ]
            ]
        ];
        $requestData = [
            'cartItem' => [
                'quote_id' => $quoteId,
                'sku' => 'grouped',
                'qty' => 1,
                'product_option' => $productOptionData
            ]
        ];
        $response = $this->_webApiCall($this->getServiceInfoAddToCart($token), $requestData);
        $this->assertArrayHasKey('product_option', $response);
        $this->assertEquals($response['product_option'], $productOptionData);

        /** @var CartRepositoryInterface $cartRepository */
        $cartRepository = $this->objectManager->get(CartRepositoryInterface::class);
        $quote = $cartRepository->get($quoteId);

        foreach ($quote->getAllItems() as $item) {
            $this->assertEquals($qtyData[$item->getProductId()], $item->getQty());
        }
        $this->createdQuotes[] = $quoteId;
    }

    /**
     * Test adding grouped product when qty for grouped_options not specified.
     *
     * @magentoApiDataFixture Magento/GroupedProduct/_files/product_grouped_with_simple.php
     * @magentoApiDataFixture Magento/Customer/_files/customer_one_address.php
     * @return void
     */
    public function testAddToCartGroupedCustomQuantityNotAllParamsSpecified(): void
    {
        $this->_markTestAsRestOnly();

        $productId = $this->productResource->getIdBySku('simple_11');

        // Get customer ID token
        /** @var CustomerTokenServiceInterface $customerTokenService */
        $customerTokenService = $this->objectManager->create(CustomerTokenServiceInterface::class);
        $token = $customerTokenService->createCustomerAccessToken(
            'customer_one_address@test.com',
            'password'
        );

        // Creating empty cart for registered customer.
        $serviceInfo = [
            'rest' => ['resourcePath' => '/V1/carts/mine', 'httpMethod' => Request::HTTP_METHOD_POST, 'token' => $token]
        ];

        $quoteId = $this->_webApiCall($serviceInfo, ['customerId' => 999]); // customerId 999 will get overridden
        $this->assertGreaterThan(0, $quoteId);

        // Adding item to the cart
        $requestData = [
            'cartItem' => [
                'quote_id' => $quoteId,
                'sku' => 'grouped',
                'qty' => 1,
                'product_option' => [
                    'extension_attributes' => [
                        'grouped_options' => [
                            ['id' => $productId],
                        ]
                    ]
                ]
            ]
        ];

        $this->createdQuotes[] = $quoteId;

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Please specify id and qty for grouped options.');

        $this->_webApiCall($this->getServiceInfoAddToCart($token), $requestData);
    }

    /**
     * Returns service info add to cart
     *
     * @param string $token
     * @return array
     */
    private function getServiceInfoAddToCart(string $token): array
    {
        return [
            'rest' => [
                'resourcePath' => '/V1/carts/mine/items',
                'httpMethod' => Request::HTTP_METHOD_POST,
                'token' => $token
            ]
        ];
    }

    /**
     * Test for product name in different store view
     *
     * @magentoConfigFixture web/url/use_store 1
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple_multistore.php
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     *
     * @param string $expectedProductName
     * @param string|null $storeCode
     *
     * @return void
     * @dataProvider dataProviderForMultiStoreView
     * @throws AuthenticationException
     */
    public function testForProductNameAsPerStoreView(string $expectedProductName, ?string $storeCode = null): void
    {
        $this->_markTestAsRestOnly();

        // Get customer ID token
        /** @var CustomerTokenServiceInterface $customerTokenService */
        $customerTokenService = $this->objectManager->create(CustomerTokenServiceInterface::class);
        $token = $customerTokenService->createCustomerAccessToken(
            'customer@example.com',
            'password'
        );

        // Creating empty cart for registered customer.
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/carts/mine',
                'httpMethod' => Request::HTTP_METHOD_POST,
                'token' => $token
            ]
        ];
        $quoteId = $this->_webApiCall($serviceInfo);
        $this->assertGreaterThan(0, $quoteId);

        // Add product to cart
        $requestData = [
            'cartItem' => [
                'quote_id' => $quoteId,
                'sku' => 'simple',
                'qty' => 1
            ]
        ];
        $this->_webApiCall($this->getServiceInfoAddToCart($token), $requestData);

        // Fetch Cart info
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/carts/mine',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
                'token' => $token
            ]
        ];
        /** @var \Magento\Quote\Api\Data\CartInterface $cart */
        $cart = $this->_webApiCall($serviceInfo, [], null, $storeCode);
        $carts = $cart['items'];
        $actualProductName = $carts[0]['name'] ?? '';

        $this->assertEquals($expectedProductName, $actualProductName);
    }

    /**
     * @return array
     */
    public static function dataProviderForMultiStoreView(): array
    {
        return [
            'noStoreCodeInRequestPath' => [
                'Simple Product One',
                null
            ],
            'defaultStoreCodeInRequestPath' => [
                'Simple Product One',
                'default'
            ],
            'secondStoreCodeInRequestPath' => [
                'StoreTitle',
                'fixturestore'
            ]
        ];
    }
}
