<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Sales;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Test\Fixture\PlaceOrder as PlaceOrderFixture;
use Magento\Checkout\Test\Fixture\SetBillingAddress as SetBillingAddressFixture;
use Magento\Checkout\Test\Fixture\SetDeliveryMethod as SetDeliveryMethodFixture;
use Magento\Checkout\Test\Fixture\SetPaymentMethod as SetPaymentMethodFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress as SetShippingAddressFixture;
use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\CustomerCart as CustomerCartFixture;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test GraphQL CustomerOrder order_date field formatting and date integrity
 * @see \Magento\SalesGraphQl\Model\Formatter\Order::format()
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OrderDateLocaleFormattingTest extends GraphQlAbstract
{
    /**
     * Default customer password used in fixtures
     */
    private const CUSTOMER_PASSWORD = 'password';

    /**
     * Regular expression pattern for order_date format (d/m/Y H:i:s)
     *
     */
    private const ORDER_DATE_REGEX_PATTERN = '/^\d{2}\/\d{2}\/\d{4} \d{2}:\d{2}:\d{2}$/';

    /**
     * Regular expression pattern for created_at format (Y-m-d H:i:s)
     *
     */
    private const CREATED_AT_REGEX_PATTERN = '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/';

    /**
     * PHP date format for order_date parsing
     */
    private const ORDER_DATE_FORMAT = 'd/m/Y H:i:s';

    /**
     * @var DataFixtureStorage
     */
    private DataFixtureStorage $fixtures;

    /**
     * @var CustomerTokenServiceInterface
     */
    private CustomerTokenServiceInterface $customerTokenService;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $objectManager = Bootstrap::getObjectManager();
        $this->fixtures = $objectManager->get(DataFixtureStorageManager::class)->getStorage();
        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
    }

    /**
     * Verify order_date field formatting with French locale
     *
     * @return void
     * @throws AuthenticationException
     */
    #[
        Config('general/locale/code', 'fr_FR'),
        Config('general/locale/timezone', 'Europe/Paris'),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(CustomerFixture::class, as: 'customer'),
        DataFixture(CustomerCartFixture::class, ['customer_id' => '$customer.id$'], as: 'quote'),
        DataFixture(
            AddProductToCartFixture::class,
            ['cart_id' => '$quote.id$', 'product_id' => '$product.id$', 'qty' => 1]
        ),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$quote.id$'], 'order')
    ]
    public function testOrderDateFormatWithFrenchLocale(): void
    {
        $customerEmail = $this->fixtures->get('customer')->getEmail();

        // Use same GraphQL query structure as manual test
        $query = $this->getCustomerOrdersQuery();
        $response = $this->graphQlQuery(
            $query,
            [],
            '',
            $this->getCustomerAuthHeaders($customerEmail, self::CUSTOMER_PASSWORD)
        );

        $this->assertArrayHasKey('customer', $response);
        $this->assertArrayHasKey('orders', $response['customer']);
        $this->assertArrayHasKey('items', $response['customer']['orders']);
        $this->assertNotEmpty($response['customer']['orders']['items']);

        $orderData = $response['customer']['orders']['items'][0];

        // Verify both aliased fields exist (matching manual test scenario)
        $this->assertArrayHasKey('orderDateOK', $orderData, 'orderDateOK (created_at alias) should exist');
        $this->assertArrayHasKey('orderDateFAIL', $orderData, 'orderDateFAIL (order_date alias) should exist');

        $orderDateOK = $orderData['orderDateOK'];
        $orderDateFAIL = $orderData['orderDateFAIL'];

        // BEFORE FIX: orderDateFAIL was in Y-m-d format (yyyy-mm-dd)
        // AFTER FIX: orderDateFAIL should be in d/m/Y H:i:s format (dd/mm/yyyy HH:ii:ss)

        // Verify orderDateFAIL is in correct format: d/m/Y H:i:s (e.g., "02/04/2025 13:35:00")
        $this->assertMatchesRegularExpression(
            self::ORDER_DATE_REGEX_PATTERN,
            $orderDateFAIL,
            sprintf(
                'orderDateFAIL should be in d/m/Y H:i:s format (dd/mm/yyyy HH:ii:ss). '
                . 'Got: %s. orderDateOK for reference: %s',
                $orderDateFAIL,
                $orderDateOK
            )
        );

        // Verify it's parseable in the expected format
        $parsedDateFAIL = \DateTime::createFromFormat(self::ORDER_DATE_FORMAT, $orderDateFAIL);
        $this->assertNotFalse(
            $parsedDateFAIL,
            sprintf(
                'orderDateFAIL "%s" should be parseable as d/m/Y H:i:s format',
                $orderDateFAIL
            )
        );

        // Verify orderDateOK remains in standard format (Y-m-d H:i:s)
        $this->assertMatchesRegularExpression(
            self::CREATED_AT_REGEX_PATTERN,
            $orderDateOK,
            'orderDateOK (created_at) should remain in Y-m-d H:i:s format'
        );
    }

    /**
     * Get GraphQL query for customer orders with field aliases
     *
     * @return string
     */
    private function getCustomerOrdersQuery(): string
    {
        return <<<QUERY
{
    customer {
        orders {
            items {
                orderDateOK: created_at
                orderDateFAIL: order_date
            }
        }
    }
}
QUERY;
    }

    /**
     * Get customer authentication headers
     *
     * @param string $email
     * @param string $password
     * @return array
     * @throws AuthenticationException
     */
    private function getCustomerAuthHeaders(string $email, string $password): array
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($email, $password);
        return ['Authorization' => 'Bearer ' . $customerToken];
    }
}
