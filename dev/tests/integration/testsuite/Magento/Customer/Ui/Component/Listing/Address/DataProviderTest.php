<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Ui\Component\Listing\Address;

use Magento\Backend\Model\Locale\Resolver;
use Magento\Customer\Model\Customer;
use Magento\Framework\Api\Filter;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Locale\ResolverInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class to test Data Provider for customer address listing
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
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @var DataProvider
     */
    private $dataProvider;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->requestMock = $this->createMock(RequestInterface::class);
        $this->dataProvider = Bootstrap::getObjectManager()->create(
            DataProvider::class,
            [
                'name' => 'customer_address_listing_data_source',
                'requestFieldName' => 'id',
                'primaryFieldName' => 'entity_id',
                'request' => $this->requestMock,
            ]
        );
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
     */
    public function testGetDataByRegion(array $filterData)
    {
        $customerId = 1;
        $locale = 'JA_jp';
        $this->initLocaleResolverMock();
        $this->localeResolverMock->method('getLocale')->willReturn($locale);

        $this->requestMock->method('getParam')->with('parent_id')->willReturn($customerId);
        $this->dataProvider = Bootstrap::getObjectManager()->create(
            DataProvider::class,
            [
                'name' => 'customer_address_listing_data_source',
                'requestFieldName' => 'id',
                'primaryFieldName' => 'entity_id',
                'request' => $this->requestMock,
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
        $this->assertEquals($filterData['value'], $data['items'][0]['region']);
    }

    /**
     * @return array
     */
    public static function getDataByRegionDataProvider(): array
    {
        return [
            [['condition_type' => 'fulltext', 'field' => 'fulltext', 'value' => 'アラバマ']],
            [['condition_type' => 'regular', 'field' => 'region', 'value' => 'アラバマ']],
        ];
    }

    /**
     * Mock locale resolver
     */
    private function initLocaleResolverMock()
    {
        $this->localeResolverMock = $this->createMock(ResolverInterface::class);
        Bootstrap::getObjectManager()->addSharedInstance($this->localeResolverMock, ResolverInterface::class);
        Bootstrap::getObjectManager()->addSharedInstance($this->localeResolverMock, Resolver::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        Bootstrap::getObjectManager()->removeSharedInstance(ResolverInterface::class);
        Bootstrap::getObjectManager()->removeSharedInstance(Resolver::class);
    }
}
