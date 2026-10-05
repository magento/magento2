<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Persistent\Test\Unit\Model\ResourceModel;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DB\Select;
use Magento\Persistent\Helper\Data;
use Magento\Persistent\Model\ResourceModel\ExpiredPersistentQuotesCollection;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\ResourceModel\Quote\Collection;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
 */
class ExpiredPersistentQuotesCollectionTest extends TestCase
{
    /**
     * @var ScopeConfigInterface|MockObject
     */
    private ScopeConfigInterface $scopeConfigMock;

    /**
     * @var CollectionFactory|MockObject
     */
    private CollectionFactory $quoteCollectionFactoryMock;

    /**
     * @var StoreInterface|MockObject
     */
    private StoreInterface $storeMock;

    /**
     * @var int
     */
    private int $batchSize = 2;

    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->quoteCollectionFactoryMock = $this->createMock(CollectionFactory::class);

        $this->storeMock = $this->createMock(StoreInterface::class);
        $this->storeMock->method('getId')->willReturn(1);
        $this->storeMock->method('getWebsiteId')->willReturn(1);

        $this->scopeConfigMock->method('getValue')
            ->with(Data::XML_PATH_LIFE_TIME, ScopeInterface::SCOPE_WEBSITE, 1)
            ->willReturn(60);
    }

    /**
     * @param int $batchSize
     * @return ExpiredPersistentQuotesCollection
     */
    private function createModel(int $batchSize): ExpiredPersistentQuotesCollection
    {
        return new ExpiredPersistentQuotesCollection(
            $this->scopeConfigMock,
            $this->storeMock,
            $this->quoteCollectionFactoryMock,
            $batchSize
        );
    }

    /**
     * Build a permissively-stubbed batch Collection mock yielding the given quotes.
     *
     * @param Quote[] $quotes
     * @return Collection|MockObject
     * @throws Exception
     */
    private function mockBatch(array $quotes): Collection|MockObject
    {
        $batchMock = $this->createMock(Collection::class);
        $batchMock->method('addFieldToFilter')->willReturnSelf();
        $batchMock->method('setOrder')->willReturnSelf();
        $batchMock->method('setPageSize')->willReturnSelf();
        $batchMock->method('getTable')->willReturn('customer_log');

        $selectMock = $this->createMock(Select::class);
        $selectMock->method('joinLeft')->willReturnSelf();
        $selectMock->method('where')->willReturnSelf();
        $batchMock->method('getSelect')->willReturn($selectMock);

        $batchMock->method('getIterator')->willReturn(new \ArrayIterator($quotes));

        return $batchMock;
    }

    /**
     * A batch mock representing the "no more rows" confirmation fetch.
     *
     * @return Collection|MockObject
     * @throws Exception
     */
    private function mockEmptyBatch(): Collection|MockObject
    {
        return $this->mockBatch([]);
    }

    /**
     * @throws Exception
     */
    public function testIterationYieldsAllQuotesInSingleBatch(): void
    {
        $quote1 = $this->createMock(Quote::class);
        $quote1->method('getId')->willReturn(101);
        $quote2 = $this->createMock(Quote::class);
        $quote2->method('getId')->willReturn(102);
        $quote3 = $this->createMock(Quote::class);
        $quote3->method('getId')->willReturn(103);

        $this->quoteCollectionFactoryMock->method('create')
            ->willReturnOnConsecutiveCalls(
                $this->mockBatch([$quote1, $quote2, $quote3]),
                $this->mockEmptyBatch()
            );

        $model = $this->createModel(10);
        $collected = [];
        $keys = [];
        foreach ($model as $key => $quote) {
            $collected[] = $quote;
            $keys[] = $key;
        }

        $this->assertSame([$quote1, $quote2, $quote3], $collected);
        $this->assertSame([101, 102, 103], $keys);
    }

    /**
     * @throws Exception
     */
    public function testIterationSpansMultipleBatchesUsingCursor(): void
    {
        $quote1 = $this->createMock(Quote::class);
        $quote1->method('getId')->willReturn(1);
        $quote2 = $this->createMock(Quote::class);
        $quote2->method('getId')->willReturn(2);
        $quote3 = $this->createMock(Quote::class);
        $quote3->method('getId')->willReturn(3);

        $this->quoteCollectionFactoryMock->expects($this->exactly(3))
            ->method('create')
            ->willReturnOnConsecutiveCalls(
                $this->mockBatch([$quote1, $quote2]),
                $this->mockBatch([$quote3]),
                $this->mockEmptyBatch()
            );

        $model = $this->createModel($this->batchSize);
        $collected = [];
        foreach ($model as $quote) {
            $collected[] = $quote;
        }

        $this->assertSame([$quote1, $quote2, $quote3], $collected);
    }

    /**
     * The lifetime config value is fixed for this iterator's store/website for its whole
     * lifetime, so it should be fetched once and reused, not re-fetched on every batch.
     *
     * @throws Exception
     */
    public function testLifetimeConfigIsFetchedOnlyOnceAcrossMultipleBatches(): void
    {
        $quote1 = $this->createMock(Quote::class);
        $quote1->method('getId')->willReturn(1);
        $quote2 = $this->createMock(Quote::class);
        $quote2->method('getId')->willReturn(2);

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Data::XML_PATH_LIFE_TIME, ScopeInterface::SCOPE_WEBSITE, 1)
            ->willReturn(60);

        $this->quoteCollectionFactoryMock->expects($this->exactly(3))
            ->method('create')
            ->willReturnOnConsecutiveCalls(
                $this->mockBatch([$quote1]),
                $this->mockBatch([$quote2]),
                $this->mockEmptyBatch()
            );

        $model = $this->createModel($this->batchSize);
        $collected = [];
        foreach ($model as $quote) {
            $collected[] = $quote;
        }

        $this->assertSame([$quote1, $quote2], $collected);
    }

    /**
     * @throws Exception
     */
    public function testCurrentAndValidWorkWithoutExplicitRewind(): void
    {
        $quote1 = $this->createMock(Quote::class);
        $quote1->method('getId')->willReturn(501);

        $this->quoteCollectionFactoryMock->method('create')
            ->willReturnOnConsecutiveCalls($this->mockBatch([$quote1]), $this->mockEmptyBatch());

        $model = $this->createModel($this->batchSize);

        $this->assertTrue($model->valid());
        $this->assertSame($quote1, $model->current());
        $this->assertSame(501, $model->key());
    }

    /**
     * Calling next() as the very first call (no prior rewind()/current()) should behave
     * like a plain PHP array whose internal pointer already starts at the first element:
     * it lands on the second item, not an uninitialized/skipped state.
     *
     * @throws Exception
     */
    public function testNextWorksWithoutExplicitRewind(): void
    {
        $quote1 = $this->createMock(Quote::class);
        $quote1->method('getId')->willReturn(501);
        $quote2 = $this->createMock(Quote::class);
        $quote2->method('getId')->willReturn(502);

        $this->quoteCollectionFactoryMock->method('create')
            ->willReturnOnConsecutiveCalls($this->mockBatch([$quote1, $quote2]), $this->mockEmptyBatch());

        $model = $this->createModel($this->batchSize);
        $model->next();

        $this->assertTrue($model->valid());
        $this->assertSame($quote2, $model->current());
        $this->assertSame(502, $model->key());
    }

    /**
     * @throws Exception
     */
    public function testEmptyResultSetProducesZeroIterations(): void
    {
        $this->quoteCollectionFactoryMock->method('create')
            ->willReturn($this->mockEmptyBatch());

        $model = $this->createModel($this->batchSize);
        $model->rewind();
        $this->assertFalse($model->valid());

        $iterations = 0;
        foreach ($model as $quote) {
            $iterations++;
        }
        $this->assertSame(0, $iterations);
    }

    /**
     * @throws Exception
     */
    public function testBatchQueryFiltersByStoreAndLifetime(): void
    {
        $quote = $this->createMock(Quote::class);
        $quote->method('getId')->willReturn(1);

        $batchMock = $this->mockBatch([$quote]);
        $batchMock->method('addFieldToFilter')
            ->willReturnCallback(function ($field) use ($batchMock) {
                static $filterCallCount = 0;
                $filterCallCount++;

                match ($filterCallCount) {
                    1 => $this->assertEquals('main_table.store_id', $field),
                    2 => $this->assertEquals('main_table.updated_at', $field),
                    3 => $this->assertEquals('main_table.is_persistent', $field),
                    4 => $this->assertEquals('main_table.entity_id', $field)
                };

                return $batchMock;
            });

        $this->quoteCollectionFactoryMock->method('create')
            ->willReturnOnConsecutiveCalls($batchMock, $this->mockEmptyBatch());

        $model = $this->createModel($this->batchSize);
        foreach ($model as $q) {
            // drain the first (real) batch
        }
    }

    /**
     * Case 1, 2, and 3 are expressed as a single OR'd condition against one join to customer_log.
     *
     * @throws Exception
     */
    public function testJoinsCustomerLogWithSingleCombinedCaseCondition(): void
    {
        $quote = $this->createMock(Quote::class);
        $quote->method('getId')->willReturn(1);

        $selectMock = $this->createMock(Select::class);
        $selectMock->expects($this->once())
            ->method('joinLeft')
            ->with(
                ['cl' => 'customer_log'],
                'cl.customer_id = main_table.customer_id',
                []
            )
            ->willReturnSelf();
        $selectMock->expects($this->once())
            ->method('where')
            ->with(
                '(cl.last_logout_at IS NOT NULL AND cl.last_login_at < cl.last_logout_at)
                OR (cl.last_login_at < "' . gmdate("Y-m-d H:i:s", time() - 60) . '"
                    AND (cl.last_logout_at IS NULL OR cl.last_login_at > cl.last_logout_at))'
            )
            ->willReturnSelf();

        $batchMock = $this->createMock(Collection::class);
        $batchMock->method('addFieldToFilter')->willReturnSelf();
        $batchMock->method('setOrder')->willReturnSelf();
        $batchMock->method('setPageSize')->willReturnSelf();
        $batchMock->method('getTable')->willReturn('customer_log');
        $batchMock->method('getSelect')->willReturn($selectMock);
        $batchMock->method('getIterator')->willReturn(new \ArrayIterator([$quote]));

        $this->quoteCollectionFactoryMock->method('create')
            ->willReturnOnConsecutiveCalls($batchMock, $this->mockEmptyBatch());

        $model = $this->createModel($this->batchSize);
        $collected = [];
        foreach ($model as $q) {
            $collected[] = $q;
        }

        $this->assertSame([$quote], $collected);
    }
}
