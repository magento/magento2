<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Ui\Component;

use Magento\Backend\Model\Locale\Resolver;
use Magento\Framework\Api\Filter;
use Magento\Framework\Locale\ResolverInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class to test Data Provider for customer listing
 *
 * @magentoAppArea adminhtml
 */
class DataProviderTest extends TestCase
{
    /**
     * @var array
     */
    private $dataProviderParams = [
        'name' => 'customer_listing_data_source',
        'requestFieldName' => 'id',
        'primaryFieldName' => 'entity_id',
    ];

    /**
     * @var ResolverInterface|MockObject
     */
    private $localeResolverMock;

    /**
     * @var DataProvider
     */
    private $dataProvider;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->initLocaleResolverMock();
        $this->dataProvider = Bootstrap::getObjectManager()->create(DataProvider::class, $this->dataProviderParams);
    }

    /**
     * Test to filter by region name in custom locale
     *
     * @param array $filterData
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     * @magentoDataFixture Magento/Directory/_files/region_name_jp.php
     * @dataProvider getDataByRegionDataProvider
     * @magentoDbIsolation disabled
     */
    public function testGetDataByRegion(array $filterData): void
    {
        $locale = 'JA_jp';
        $this->localeResolverMock->method('getLocale')->willReturn($locale);

        $filter = Bootstrap::getObjectManager()->create(Filter::class, ['data' => $filterData]);
        $this->dataProvider->addFilter($filter);
        $data = $this->dataProvider->getData();
        $this->assertEquals(1, $data['totalRecords']);
        $this->assertCount(1, $data['items']);
        $this->assertEquals($filterData['value'], $data['items'][0]['billing_region']);
    }

    /**
     * @return array
     */
    public function getDataByRegionDataProvider(): array
    {
        return [
            [['condition_type' => 'fulltext', 'field' => 'fulltext', 'value' => 'アラバマ']],
            [['condition_type' => 'regular', 'field' => 'billing_region', 'value' => 'アラバマ']],
        ];
    }

    /**
     * Test exact search by email
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @dataProvider exactSearchByEmailDataProvider
     * @magentoDbIsolation disabled
     *
     * @param string $searchEmail
     * @param int $expectedCount
     * @return void
     */
    public function testGetCustomersByEmail(string $searchEmail, int $expectedCount): void
    {
        $filter = Bootstrap::getObjectManager()->create(
            Filter::class,
            ['data' => ['condition_type' => 'fulltext', 'field' => 'fulltext', 'value' => $searchEmail]]
        );
        $this->dataProvider->addFilter($filter);
        $data = $this->dataProvider->getData();

        $this->assertCount($expectedCount, $data['items']);
    }

    /**
     * @return array
     */
    public function exactSearchByEmailDataProvider(): array
    {
        return [
            'exact search' => ['"customer@example.com"', 1],
            'double quote not in the end' => ['"customer@example.co"m', 0],
            'double quote not in the start' => ['c"ustomer@example.com"', 0],
        ];
    }

    /**
     * Mock locale resolver
     */
    private function initLocaleResolverMock(): void
    {
        $this->localeResolverMock = $this->createMock(ResolverInterface::class);
        Bootstrap::getObjectManager()->removeSharedInstance(ResolverInterface::class);
        Bootstrap::getObjectManager()->removeSharedInstance(Resolver::class);
        Bootstrap::getObjectManager()->addSharedInstance($this->localeResolverMock, ResolverInterface::class);
        Bootstrap::getObjectManager()->addSharedInstance($this->localeResolverMock, Resolver::class);
    }
}
