<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Persistent\Test\Unit\Model\ResourceModel;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Persistent\Model\ResourceModel\ExpiredPersistentQuotesCollection;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\ResourceModel\Quote\Collection;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Persistent\Helper\Data;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\DB\Select;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class ExpiredPersistentQuotesCollectionTest extends TestCase
{
    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfigMock;

    /**
     * @var CollectionFactory
     */
    private CollectionFactory $quoteCollectionFactoryMock;

    /**
     * @var ExpiredPersistentQuotesCollection
     */
    private ExpiredPersistentQuotesCollection $model;

    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->quoteCollectionFactoryMock = $this->createMock(CollectionFactory::class);

        $this->model = new ExpiredPersistentQuotesCollection(
            $this->scopeConfigMock,
            $this->quoteCollectionFactoryMock
        );
    }

    /**
     * Test getExpiredPersistentQuotes method
     *
     * @return void
     * @throws Exception
     */
    public function testGetExpiredPersistentQuotes(): void
    {
        $storeMock = $this->createMock(StoreInterface::class);
        $storeMock->method('getId')->willReturn(1);
        $storeMock->method('getWebsiteId')->willReturn(1);

        $this->scopeConfigMock->method('getValue')
            ->with(Data::XML_PATH_LIFE_TIME, ScopeInterface::SCOPE_WEBSITE, 1)
            ->willReturn(60);

        $quoteCollectionMock = $this->createMock(Collection::class);
        $this->quoteCollectionFactoryMock->method('create')->willReturn($quoteCollectionMock);

        $quoteCollectionMock->method('addFieldToFilter')
            ->willReturnCallback(function ($field) use ($quoteCollectionMock) {
                static $filterCallCount = 0;
                $filterCallCount++;

                match ($filterCallCount) {
                    1 => $this->assertEquals('main_table.store_id', $field),
                    2 => $this->assertEquals('main_table.updated_at', $field),
                    3 => $this->assertEquals('main_table.is_persistent', $field),
                    4 => $this->assertEquals('main_table.entity_id', $field)
                };

                return $quoteCollectionMock;
            });

        $quoteCollectionMock->method('setOrder')
            ->with('entity_id', Collection::SORT_ORDER_ASC)
            ->willReturnSelf();

        $quoteCollectionMock->method('setPageSize')
            ->with($this->isType('integer'))
            ->willReturnSelf();

        $dbSelectMock1 = $this->createMock(Select::class);
        $dbSelectMock2 = $this->createMock(Select::class);
        $dbSelectMock3 = $this->createMock(Select::class);
        $quoteCollectionMock->method('getSelect')
            ->willReturnOnConsecutiveCalls($dbSelectMock1, $dbSelectMock2, $dbSelectMock3);
        $quoteCollectionMock->method('getTable')
            ->willReturn('customer_log');

        $dbSelectMock1->method('reset')
            ->with(Select::COLUMNS)
            ->willReturn($dbSelectMock1);
        $dbSelectMock1->method('columns')->willReturnSelf();
        $dbSelectMock1->method('joinLeft')
            ->with(
                ['cl1' => 'customer_log'],
                'cl1.customer_id = main_table.customer_id',
                []
            )
            ->willReturnSelf();
        $dbSelectMock1->method('where')
            ->with('cl1.last_login_at < cl1.last_logout_at
            AND cl1.last_logout_at IS NOT NULL')
            ->willReturnSelf();

        $dbSelectMock2->method('reset')
            ->with(Select::COLUMNS)
            ->willReturn($dbSelectMock2);
        $dbSelectMock2->method('columns')->willReturnSelf();
        $dbSelectMock2->method('joinLeft')
            ->with(
                ['cl2' => 'customer_log'],
                'cl2.customer_id = main_table.customer_id',
                []
            )
            ->willReturnSelf();
        $dbSelectMock2->method('where')
            ->with('cl2.last_login_at < "' . gmdate("Y-m-d H:i:s", time() - 60) . '"
        AND (cl2.last_logout_at IS NULL OR cl2.last_login_at > cl2.last_logout_at)')
            ->willReturnSelf();

        $dbSelectMockUnion = $this->createMock(Select::class);
        $connectionMock = $this->createMock(AdapterInterface::class);
        $quoteCollectionMock->method('getConnection')->willReturn($connectionMock);
        $connectionMock->method('select')->willReturn($dbSelectMockUnion);
        $dbSelectMockUnion->method('union')
            ->with([$dbSelectMock1, $dbSelectMock2], Select::SQL_UNION_ALL)
            ->willReturn($dbSelectMockUnion);

        $dbSelectMockUnion->method('where')
            ->with($this->stringContains('main_table.entity_id IN ('))
            ->willReturnSelf();

        $result = $this->model->getExpiredPersistentQuotes($storeMock, 0, 100);
        $this->assertSame($quoteCollectionMock, $result);
    }
}
