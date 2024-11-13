<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Ui\Component;

use Magento\Backend\Model\Locale\Resolver;
use Magento\Customer\Model\Customer;
use Magento\Framework\Api\Filter;
use Magento\Framework\Indexer\IndexerRegistry;
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
        $indexerRegistry = Bootstrap::getObjectManager()->create(IndexerRegistry::class);
        $indexer = $indexerRegistry->get(Customer::CUSTOMER_GRID_INDEXER_ID);
        $indexer->reindexAll();
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
    public function testGetDataByRegion(array $filterData)
    {
        $locale = 'JA_jp';
        $this->localeResolverMock->method('getLocale')->willReturn($locale);
        $this->dataProvider = Bootstrap::getObjectManager()->create(
            DataProvider::class,
            [
                'name' => 'customer_listing_data_source',
                'requestFieldName' => 'id',
                'primaryFieldName' => 'entity_id',
            ]
        );

        $filter = Bootstrap::getObjectManager()->create(
            Filter::class,
            ['data' => $filterData]
        );
        $this->dataProvider->addFilter($filter);
        $data = $this->dataProvider->getData();
        $this->assertEquals(1, $data['totalRecords']);
        $this->assertCount(1, $data['items']);
        $this->assertEquals($filterData['value'], $data['items'][0]['billing_region']);
    }

    /**
     * @return array
     */
    public static function getDataByRegionDataProvider(): array
    {
        return [
            [['condition_type' => 'fulltext', 'field' => 'fulltext', 'value' => 'アラバマ']],
            [['condition_type' => 'regular', 'field' => 'billing_region', 'value' => 'アラバマ']],
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
