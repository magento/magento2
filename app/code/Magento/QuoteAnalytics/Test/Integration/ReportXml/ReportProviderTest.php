<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\QuoteAnalytics\Test\Integration\ReportXml;

use Magento\Analytics\ReportXml\ConnectionFactory;
use Magento\Analytics\ReportXml\ReportProvider;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\App\ResourceConnection;
use Magento\Quote\Test\Fixture\CustomerCart;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Verifies that the Advanced Reporting "quotes" export bundle includes the customer name attributes.
 *
 * The customer first, middle and last names are declared in Magento/QuoteAnalytics/etc/reports.xml so that
 * Advanced Reporting can display the customer name instead of the encoded customer id. Removing any of those
 * <attribute> declarations drops the corresponding column from the generated SELECT and fails this test.
 *
 * @magentoAppArea adminhtml
 */
class ReportProviderTest extends TestCase
{
    private const REPORT_NAME = 'quotes';

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
     * The quote customer name attributes must be present in the quotes report and hold the fixture values.
     *
     * The cart is created for a customer, which copies the customer first, middle and last name onto the quote's
     * customer_firstname / customer_middlename / customer_lastname columns exported to Advanced Reporting.
     */
    #[
        DbIsolation(true),
        DataFixture(
            Customer::class,
            ['firstname' => 'John', 'middlename' => 'A', 'lastname' => 'Smith'],
            as: 'customer'
        ),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], as: 'quote'),
    ]
    public function testQuoteCustomerNameIsExported(): void
    {
        $customerId = (int)$this->fixtures->get('customer')->getId();

        $row = null;
        foreach ($this->reportProvider->getReport(self::REPORT_NAME) as $item) {
            foreach (['customer_firstname', 'customer_middlename', 'customer_lastname'] as $attribute) {
                $this->assertArrayHasKey(
                    $attribute,
                    $item,
                    sprintf('%s attribute is missing from the quotes report.', $attribute)
                );
            }
            if ((int)$item['customer_id'] === $customerId) {
                $row = $item;
            }
        }

        $this->assertNotNull($row, 'The fixture quote was not found in the quotes report.');
        $this->assertSame('John', $row['customer_firstname']);
        $this->assertSame('A', $row['customer_middlename']);
        $this->assertSame('Smith', $row['customer_lastname']);
    }
}
