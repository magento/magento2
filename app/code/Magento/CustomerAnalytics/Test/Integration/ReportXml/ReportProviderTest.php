<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CustomerAnalytics\Test\Integration\ReportXml;

use Magento\Analytics\ReportXml\ConnectionFactory;
use Magento\Analytics\ReportXml\ReportProvider;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\App\ResourceConnection;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Verifies that the Advanced Reporting "customers" export bundle includes the customer name attributes.
 *
 * The customer first, middle and last names are declared in Magento/CustomerAnalytics/etc/reports.xml so that
 * Advanced Reporting can display the customer name instead of the encoded customer id. Removing any of those
 * <attribute> declarations drops the corresponding column from the generated SELECT and fails this test.
 *
 * @magentoAppArea adminhtml
 */
class ReportProviderTest extends TestCase
{
    private const REPORT_NAME = 'customers';

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
     * The customer name attributes must be present in the customers report and hold the fixture values.
     */
    #[
        DbIsolation(true),
        DataFixture(
            Customer::class,
            ['firstname' => 'John', 'middlename' => 'A', 'lastname' => 'Smith'],
            as: 'customer'
        ),
    ]
    public function testCustomerNameIsExported(): void
    {
        $customerId = (int)$this->fixtures->get('customer')->getId();

        $row = null;
        foreach ($this->reportProvider->getReport(self::REPORT_NAME) as $item) {
            if ((int)$item['entity_id'] === $customerId) {
                $row = $item;
                break;
            }
        }

        $this->assertNotNull($row, 'The fixture customer was not found in the customers report.');
        $this->assertArrayHasKey('firstname', $row, 'firstname attribute is missing from the customers report.');
        $this->assertArrayHasKey('middlename', $row, 'middlename attribute is missing from the customers report.');
        $this->assertArrayHasKey('lastname', $row, 'lastname attribute is missing from the customers report.');
        $this->assertSame('John', $row['firstname']);
        $this->assertSame('A', $row['middlename']);
        $this->assertSame('Smith', $row['lastname']);
    }
}
