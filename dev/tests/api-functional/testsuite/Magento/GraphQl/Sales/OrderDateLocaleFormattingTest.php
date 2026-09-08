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
 * Test GraphQL CustomerOrder order_date field calendar-value correctness under non-en_US locales
 *
 * Regression coverage for AC-18084: Timezone::date() misparses machine-generated
 * DB timestamps via IntlDateFormatter::parse() under non-en_US locales (e.g. fr_FR)
 * on PHP 8.4+/ICU 78.x, silently returning the wrong calendar date. This test
 * asserts the actual calendar value, not just the output string's shape - a
 * format-only check (as this test previously did) does not catch that bug.
 *
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
     * Regular expression pattern for order_date format (Y-m-d H:i:s)
     *
     */
    private const ORDER_DATE_REGEX_PATTERN = '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/';

    /**
     * Regular expression pattern for created_at format (Y-m-d H:i:s)
     *
     */
    private const CREATED_AT_REGEX_PATTERN = '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/';

    /**
     * PHP date format for order_date / created_at parsing
     */
    private const ORDER_DATE_FORMAT = 'Y-m-d H:i:s';

    /**
     * Store timezone used by this test's locale fixture
     */
    private const STORE_TIMEZONE = 'Europe/Paris';

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
     * Verify order_date returns the correct calendar date/time under the French locale
     *
     * @return void
     * @throws AuthenticationException
     */
    #[
        Config('general/locale/code', 'fr_FR'),
        Config('general/locale/timezone', self::STORE_TIMEZONE),
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
    public function testOrderDateCalendarValueIsCorrectUnderFrenchLocale(): void
    {
        $customerEmail = $this->fixtures->get('customer')->getEmail();

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

        $this->assertArrayHasKey('createdAtRaw', $orderData, 'createdAtRaw (created_at alias) should exist');
        $this->assertArrayHasKey('orderDate', $orderData, 'orderDate (order_date alias) should exist');

        $createdAtUtc = $orderData['createdAtRaw'];
        $orderDate = $orderData['orderDate'];

        // Verify order_date is in the unambiguous ISO format (Y-m-d H:i:s)
        $this->assertMatchesRegularExpression(
            self::ORDER_DATE_REGEX_PATTERN,
            $orderDate,
            sprintf('order_date should be in Y-m-d H:i:s format. Got: %s', $orderDate)
        );

        // Verify created_at remains in the standard format (Y-m-d H:i:s)
        $this->assertMatchesRegularExpression(
            self::CREATED_AT_REGEX_PATTERN,
            $createdAtUtc,
            'created_at should remain in Y-m-d H:i:s format'
        );

        // Independently derive the expected value - deliberately does NOT go
        // through Timezone::date()/IntlDateFormatter, so it can detect a wrong
        // calendar value rather than re-deriving the same bug.
        $expectedOrderDate = (new \DateTime($createdAtUtc, new \DateTimeZone('UTC')))
            ->setTimezone(new \DateTimeZone(self::STORE_TIMEZONE))
            ->format(self::ORDER_DATE_FORMAT);

        $this->assertSame(
            $expectedOrderDate,
            $orderDate,
            sprintf(
                'order_date should equal created_at (%s, UTC) converted to the %s store timezone '
                . 'under the fr_FR locale - a mismatch indicates Timezone::date() mis-parsed the '
                . 'raw DB timestamp (AC-18084 regression).',
                $createdAtUtc,
                self::STORE_TIMEZONE
            )
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
                createdAtRaw: created_at
                orderDate: order_date
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
