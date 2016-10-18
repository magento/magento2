<?php
/**
 * @copyright {}
 */

namespace Magento\Quote\Api;

use Magento\Framework\App\Config;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Class CartManagementTest
 * @package Magento\Quote\Api
 * @magentoAppIsolation enabled
 */
class CartManagementTest extends WebapiAbstract
{
    const SERVICE_VERSION = 'V1';
    const SERVICE_NAME = 'quoteCartManagementV1';
    const RESOURCE_PATH = '/V1/carts/';
    const RESOURCE_PATH_CUSTOMER_TOKEN = "/V1/integration/customer/token";

    protected $createdQuotes = [];

    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $appConfig = $this->objectManager->get(Config::class);
        $appConfig->clean();
    }

    public function tearDown()
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->objectManager->create('Magento\Quote\Model\Quote');
        foreach ($this->createdQuotes as $quoteId) {
            $quote->load($quoteId);
            $quote->delete();
        }
    }

    public function testCreateEmptyCartForGuest()
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'CreateEmptyCart',
            ],
        ];

        $requestData = ['storeId' => 1];
        $quoteId = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertGreaterThan(0, $quoteId);
        $this->createdQuotes[] = $quoteId;
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testCreateEmptyCartForCustomer()
    {
        /** @var $repository \Magento\Customer\Api\CustomerRepositoryInterface */
        $repository = $this->objectManager->create('Magento\Customer\Api\CustomerRepositoryInterface');
        /** @var $customer \Magento\Customer\Api\Data\CustomerInterface */
        $customer = $repository->getById(1);
        $customerId = $customer->getId();

        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/customers/' . $customerId . '/carts',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'CreateEmptyCartForCustomer',
            ],
        ];

        $quoteId = $this->_webApiCall($serviceInfo, ['customerId' => $customerId]);
        $this->assertGreaterThan(0, $quoteId);
        $this->createdQuotes[] = $quoteId;
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testCreateEmptyCartAndGetCartForCustomer()
    {
        $this->_markTestAsRestOnly();

        // get customer ID token
        /** @var \Magento\Integration\Api\CustomerTokenServiceInterface $customerTokenService */
        $customerTokenService = $this->objectManager->create(
            'Magento\Integration\Api\CustomerTokenServiceInterface'
        );
        $token = $customerTokenService->createCustomerAccessToken('customer@example.com', 'password');

        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/carts/mine',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
                'token' => $token
            ]
        ];

        $quoteId = $this->_webApiCall($serviceInfo, ['customerId' => 999]); // customerId 999 will get overridden
        $this->assertGreaterThan(0, $quoteId);
        $this->createdQuotes[] = $quoteId;

        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/carts/mine',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
                'token' => $token
            ]
        ];

        /** @var \Magento\Quote\Api\Data\CartInterface $cart */
        $cart = $this->_webApiCall($serviceInfo, ['customerId' => 999]); // customerId 999 will get overridden
        $this->assertEquals($quoteId, $cart['id']);
    }

    /**
     * @magentoApiDataFixture Magento/Sales/_files/quote.php
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testAssignCustomer()
    {
        /** @var $quote \Magento\Quote\Model\Quote */
        $quote = $this->objectManager->create('Magento\Quote\Model\Quote')->load('test01', 'reserved_order_id');
        $cartId = $quote->getId();
        /** @var $repository \Magento\Customer\Api\CustomerRepositoryInterface */
        $repository = $this->objectManager->create('Magento\Customer\Api\CustomerRepositoryInterface');
        /** @var $customer \Magento\Customer\Api\Data\CustomerInterface */
        $customer = $repository->getById(1);
        $customerId = $customer->getId();

        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/carts/' . $cartId,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => 'V1',
                'operation' => self::SERVICE_NAME . 'AssignCustomer',
            ],
        ];

        $requestData = [
            'cartId' => $cartId,
            'customerId' => $customerId,
            'storeId' => 1,
        ];
        // Cart must be anonymous (see fixture)
        $this->assertEmpty($quote->getCustomerId());

        $this->assertTrue($this->_webApiCall($serviceInfo, $requestData));
        // Reload target quote
        $quote = $this->objectManager->create('Magento\Quote\Model\Quote')->load('test01', 'reserved_order_id');
        $this->assertEquals(0, $quote->getCustomerIsGuest());
        $this->assertEquals($customer->getId(), $quote->getCustomerId());
        $this->assertEquals($customer->getFirstname(), $quote->getCustomerFirstname());
        $this->assertEquals($customer->getLastname(), $quote->getCustomerLastname());
    }

    /**
     * @magentoApiDataFixture Magento/Sales/_files/quote.php
     * @expectedException \Exception
     */
    public function testAssignCustomerThrowsExceptionIfThereIsNoCustomerWithGivenId()
    {
        /** @var $quote \Magento\Quote\Model\Quote */
        $quote = $this->objectManager->create('Magento\Quote\Model\Quote')->load('test01', 'reserved_order_id');
        $cartId = $quote->getId();
        $customerId = 9999;
        $serviceInfo = [
            'soap' => [
                'serviceVersion' => 'V1',
                'service' => self::SERVICE_NAME,
                'operation' => self::SERVICE_NAME . 'AssignCustomer',
            ],
            'rest' => [
                'resourcePath' => '/V1/carts/' . $cartId,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT,
            ],
        ];
        $requestData = [
            'cartId' => $cartId,
            'customerId' => $customerId,
            'storeId' => 1,
        ];

        $this->_webApiCall($serviceInfo, $requestData);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @expectedException \Exception
     */
    public function testAssignCustomerThrowsExceptionIfThereIsNoCartWithGivenId()
    {
        $cartId = 9999;
        $customerId = 1;
        $serviceInfo = [
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => 'V1',
                'operation' => self::SERVICE_NAME . 'AssignCustomer',
            ],
            'rest' => [
                'resourcePath' => '/V1/carts/' . $cartId,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT,
            ],
        ];
        $requestData = [
            'cartId' => $cartId,
            'customerId' => $customerId,
            'storeId' => 1,
        ];

        $this->_webApiCall($serviceInfo, $requestData);
    }

    /**
     * @magentoApiDataFixture Magento/Sales/_files/quote_with_customer.php
     * @expectedException \Exception
     * @expectedExceptionMessage Cannot assign customer to the given cart. The cart is not anonymous.
     */
    public function testAssignCustomerThrowsExceptionIfTargetCartIsNotAnonymous()
    {
        /** @var $customer \Magento\Customer\Model\Customer */
        $customer = $this->objectManager->create('Magento\Customer\Model\Customer')->load(1);
        $customerId = $customer->getId();
        /** @var $quote \Magento\Quote\Model\Quote */
        $quote = $this->objectManager->create('Magento\Quote\Model\Quote')->load('test01', 'reserved_order_id');
        $cartId = $quote->getId();

        $serviceInfo = [
            'rest' => [
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT,
                'resourcePath' => '/V1/carts/' . $cartId,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => 'V1',
                'operation' => self::SERVICE_NAME . 'AssignCustomer',
            ],
        ];

        $requestData = [
            'cartId' => $cartId,
            'customerId' => $customerId,
            'storeId' => 1,
        ];
        $this->_webApiCall($serviceInfo, $requestData);
    }

    /**
     * @magentoApiDataFixture Magento/Sales/_files/quote.php
     * @magentoApiDataFixture Magento/Customer/_files/customer_non_default_website_id.php
     * @expectedException \Exception
     * @expectedExceptionMessage Cannot assign customer to the given cart. The cart belongs to different store.
     */
    public function testAssignCustomerThrowsExceptionIfCartIsAssignedToDifferentStore()
    {
        $repository = $this->objectManager->create('Magento\Customer\Api\CustomerRepositoryInterface');
        /** @var $customer \Magento\Customer\Api\Data\CustomerInterface */
        $customer = $repository->getById(1);
        /** @var $quote \Magento\Quote\Model\Quote */
        $quote = $this->objectManager->create('Magento\Quote\Model\Quote')->load('test01', 'reserved_order_id');

        $customerId = $customer->getId();
        $cartId = $quote->getId();

        $serviceInfo = [
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => 'V1',
                'operation' => self::SERVICE_NAME . 'AssignCustomer',
            ],
            'rest' => [
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT,
                'resourcePath' => '/V1/carts/' . $cartId,
            ],
        ];

        $requestData = [
            'cartId' => $cartId,
            'customerId' => $customerId,
            'storeId' => 1,
        ];
        $this->_webApiCall($serviceInfo, $requestData);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     * @magentoApiDataFixture Magento/Sales/_files/quote.php
     * @expectedException \Exception
     * @expectedExceptionMessage Cannot assign customer to the given cart. Customer already has active cart.
     */
    public function testAssignCustomerThrowsExceptionIfCustomerAlreadyHasActiveCart()
    {
        /** @var $customer \Magento\Customer\Model\Customer */
        $customer = $this->objectManager->create('Magento\Customer\Model\Customer')->load(1);
        // Customer has a quote with reserved order ID test_order_1 (see fixture)
        /** @var $customerQuote \Magento\Quote\Model\Quote */
        $customerQuote = $this->objectManager->create('Magento\Quote\Model\Quote')
            ->load('test_order_1', 'reserved_order_id');
        $customerQuote->setIsActive(1)->save();
        /** @var $quote \Magento\Quote\Model\Quote */
        $quote = $this->objectManager->create('Magento\Quote\Model\Quote')->load('test01', 'reserved_order_id');

        $cartId = $quote->getId();
        $customerId = $customer->getId();

        $serviceInfo = [
            'soap' => [
                'service' => self::SERVICE_NAME,
                'operation' => self::SERVICE_NAME . 'AssignCustomer',
                'serviceVersion' => 'V1',
            ],
            'rest' => [
                'resourcePath' => '/V1/carts/' . $cartId,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT,
            ],
        ];

        $requestData = [
            'cartId' => $cartId,
            'customerId' => $customerId,
            'storeId' => 1,
        ];
        $this->_webApiCall($serviceInfo, $requestData);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_check_payment.php
     */
    public function testPlaceOrder()
    {
        /** @var $quote \Magento\Quote\Model\Quote */
        $quote = $this->objectManager->create('Magento\Quote\Model\Quote')->load('test_order_1', 'reserved_order_id');
        $cartId = $quote->getId();

        $serviceInfo = [
            'soap' => [
                'service' => 'quoteCartManagementV1',
                'operation' => 'quoteCartManagementV1PlaceOrder',
                'serviceVersion' => 'V1',
            ],
            'rest' => [
                'resourcePath' => '/V1/carts/' . $cartId . '/order',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT,
            ],
        ];

        $orderId = $this->_webApiCall($serviceInfo, ['cartId' => $cartId]);

        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->objectManager->create('Magento\Sales\Model\Order')->load($orderId);
        $items = $order->getAllItems();
        $this->assertCount(1, $items);
        $this->assertEquals('Simple Product', $items[0]->getName());
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_check_payment.php
     */
    public function testPlaceOrderForMyCart()
    {
        $this->_markTestAsRestOnly();

        // get customer ID token
        /** @var \Magento\Integration\Api\CustomerTokenServiceInterface $customerTokenService */
        $customerTokenService = $this->objectManager->create(
            'Magento\Integration\Api\CustomerTokenServiceInterface'
        );
        $token = $customerTokenService->createCustomerAccessToken('customer@example.com', 'password');

        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/carts/mine/order',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT,
                'token' => $token
            ],
        ];

        $orderId = $this->_webApiCall($serviceInfo, []);

        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->objectManager->create('Magento\Sales\Model\Order')->load($orderId);
        $items = $order->getAllItems();
        $this->assertCount(1, $items);
        $this->assertEquals('Simple Product', $items[0]->getName());
    }

    /**
     * Test to get my cart based on customer authentication token or session
     *
     * @magentoApiDataFixture Magento/Sales/_files/quote_with_customer.php
     */
    public function testGetCartForCustomer()
    {
        // get customer ID token
        /** @var \Magento\Integration\Api\CustomerTokenServiceInterface $customerTokenService */
        $customerTokenService = $this->objectManager->create(
            'Magento\Integration\Api\CustomerTokenServiceInterface'
        );
        $token = $customerTokenService->createCustomerAccessToken('customer@example.com', 'password');

        $cart = $this->getCart('test01');
        $customerId = $cart->getCustomer()->getId();

        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/carts/mine',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
                'token' => $token
            ],
            'soap' => [
                'service' => 'quoteCartManagementV1',
                'serviceVersion' => 'V1',
                'operation' => 'quoteCartManagementV1GetCartForCustomer',
                'token' => $token
            ],
        ];

        $requestData = ['customerId' => $customerId];
        $cartData = $this->_webApiCall($serviceInfo, $requestData);

        $this->assertEquals($cart->getId(), $cartData['id']);
        $this->assertEquals($cart->getCreatedAt(), $cartData['created_at']);
        $this->assertEquals($cart->getUpdatedAt(), $cartData['updated_at']);
        $this->assertEquals($cart->getIsActive(), $cartData['is_active']);
        $this->assertEquals($cart->getIsVirtual(), $cartData['is_virtual']);
        $this->assertEquals($cart->getOrigOrderId(), $cartData['orig_order_id']);
        $this->assertEquals($cart->getItemsCount(), $cartData['items_count']);
        $this->assertEquals($cart->getItemsQty(), $cartData['items_qty']);

        $this->assertContains('customer', $cartData);
        $this->assertEquals(false, $cartData['customer_is_guest']);
        $this->assertContains('currency', $cartData);
        $this->assertEquals($cart->getGlobalCurrencyCode(), $cartData['currency']['global_currency_code']);
        $this->assertEquals($cart->getBaseCurrencyCode(), $cartData['currency']['base_currency_code']);
        $this->assertEquals($cart->getQuoteCurrencyCode(), $cartData['currency']['quote_currency_code']);
        $this->assertEquals($cart->getStoreCurrencyCode(), $cartData['currency']['store_currency_code']);
        $this->assertEquals($cart->getBaseToGlobalRate(), $cartData['currency']['base_to_global_rate']);
        $this->assertEquals($cart->getBaseToQuoteRate(), $cartData['currency']['base_to_quote_rate']);
        $this->assertEquals($cart->getStoreToBaseRate(), $cartData['currency']['store_to_base_rate']);
        $this->assertEquals($cart->getStoreToQuoteRate(), $cartData['currency']['store_to_quote_rate']);
    }

    /**
     * Retrieve quote by given reserved order ID
     *
     * @param string $reservedOrderId
     * @return \Magento\Quote\Model\Quote
     * @throws \InvalidArgumentException
     */
    protected function getCart($reservedOrderId)
    {
        /** @var $cart \Magento\Quote\Model\Quote */
        $cart = $this->objectManager->get('Magento\Quote\Model\Quote');
        $cart->load($reservedOrderId, 'reserved_order_id');
        if (!$cart->getId()) {
            throw new \InvalidArgumentException('There is no quote with provided reserved order ID.');
        }
        return $cart;
    }
}
