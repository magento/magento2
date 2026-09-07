<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesAnalytics\Test\Integration\ReportXml;

use Magento\Analytics\ReportXml\ConnectionFactory;
use Magento\Analytics\ReportXml\ReportProvider;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Test\Fixture\PlaceOrder as PlaceOrderFixture;
use Magento\Checkout\Test\Fixture\SetBillingAddress as SetBillingAddressFixture;
use Magento\Checkout\Test\Fixture\SetDeliveryMethod as SetDeliveryMethodFixture;
use Magento\Checkout\Test\Fixture\SetPaymentMethod as SetPaymentMethodFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress as SetShippingAddressFixture;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\App\ResourceConnection;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\CustomerCart;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Verifies that the Advanced Reporting "orders" and "order_addresses" export bundles include the name attributes.
 *
 * The order customer name (sales_order) and the address name (sales_order_address) attributes are declared in
 * Magento/SalesAnalytics/etc/reports.xml so that Advanced Reporting can display the customer name instead of the
 * encoded customer id. Removing any of those <attribute> declarations drops the corresponding column from the
 * generated SELECT and fails these tests.
 *
 * @magentoAppArea adminhtml
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ReportProviderTest extends TestCase
{
    /**
     * @var ReportProvider
     */
    private $reportProvider;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $objectManager = Bootstrap::getObjectManager();
        $connection = $objectManager->get(ResourceConnection::class)
            ->getConnection(ResourceConnection::DEFAULT_CONNECTION);
        $connectionFactory = $this->createMock(ConnectionFactory::class);
        $connectionFactory->method('getConnection')->willReturn($connection);
        $this->reportProvider = $objectManager->create(
            ReportProvider::class,
            ['connectionFactory' => $connectionFactory]
        );
        $this->fixtures = $objectManager->get(DataFixtureStorageManager::class)->getStorage();
    }

    /**
     * The order customer name attributes must be present in the orders report and hold the fixture values.
     */
    #[
        DbIsolation(true),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(
            Customer::class,
            ['firstname' => 'John', 'middlename' => 'A', 'lastname' => 'Smith'],
            as: 'customer'
        ),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], as: 'quote'),
        DataFixture(
            AddProductToCartFixture::class,
            ['cart_id' => '$quote.id$', 'product_id' => '$product.id$', 'qty' => 1]
        ),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$quote.id$'], as: 'order'),
    ]
    public function testOrderCustomerNameIsExported(): void
    {
        $orderId = (int)$this->fixtures->get('order')->getEntityId();

        $row = null;
        foreach ($this->getReportRows('orders') as $item) {
            foreach (['customer_firstname', 'customer_middlename', 'customer_lastname'] as $attribute) {
                $this->assertArrayHasKey(
                    $attribute,
                    $item,
                    sprintf('%s attribute is missing from the orders report.', $attribute)
                );
            }
            if ((int)$item['entity_id'] === $orderId) {
                $row = $item;
            }
        }

        $this->assertNotNull($row, 'The fixture order was not found in the orders report.');
        $this->assertSame('John', $row['customer_firstname']);
        $this->assertSame('A', $row['customer_middlename']);
        $this->assertSame('Smith', $row['customer_lastname']);
    }

    /**
     * The order address name attributes must be present in the order_addresses report and hold the fixture values.
     */
    #[
        DbIsolation(true),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(Customer::class, as: 'customer'),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], as: 'quote'),
        DataFixture(
            AddProductToCartFixture::class,
            ['cart_id' => '$quote.id$', 'product_id' => '$product.id$', 'qty' => 1]
        ),
        DataFixture(
            SetBillingAddressFixture::class,
            [
                'cart_id' => '$quote.id$',
                'address' => ['firstname' => 'Jane', 'middlename' => 'Q', 'lastname' => 'Public'],
            ]
        ),
        DataFixture(
            SetShippingAddressFixture::class,
            [
                'cart_id' => '$quote.id$',
                'address' => ['firstname' => 'Jane', 'middlename' => 'Q', 'lastname' => 'Public'],
            ]
        ),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$quote.id$'], as: 'order'),
    ]
    public function testOrderAddressNameIsExported(): void
    {
        $row = null;
        foreach ($this->getReportRows('order_addresses') as $item) {
            foreach (['firstname', 'middlename', 'lastname'] as $attribute) {
                $this->assertArrayHasKey(
                    $attribute,
                    $item,
                    sprintf('%s attribute is missing from the order_addresses report.', $attribute)
                );
            }
            if ($item['firstname'] === 'Jane' && $item['lastname'] === 'Public') {
                $row = $item;
            }
        }

        $this->assertNotNull($row, 'The fixture order address was not found in the order_addresses report.');
        $this->assertSame('Jane', $row['firstname']);
        $this->assertSame('Q', $row['middlename']);
        $this->assertSame('Public', $row['lastname']);
    }

    /**
     * Returns all rows produced by the given report.
     *
     * @param string $reportName
     * @return array
     */
    private function getReportRows(string $reportName): array
    {
        $rows = [];
        foreach ($this->reportProvider->getReport($reportName) as $row) {
            $rows[] = $row;
        }
        $this->assertNotEmpty($rows, sprintf('The %s report returned no rows.', $reportName));

        return $rows;
    }
}
