<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Cron;

use Magento\Framework\DB\Select;
use Magento\Quote\Model\ResourceModel\Quote\Collection as QuoteCollection;
use Magento\Sales\Cron\CleanExpiredQuotes;
use Magento\Sales\Model\ResourceModel\Collection\ExpiredQuotesCollection;
use Magento\Sales\Model\ResourceModel\Quote\Delete;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Unit test for CleanExpiredQuotes cron job.
 */
class CleanExpiredQuotesTest extends TestCase
{
    private const BATCH_SIZE = 3;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private StoreManagerInterface|MockObject $storeManager;

    /**
     * @var ExpiredQuotesCollection|MockObject
     */
    private ExpiredQuotesCollection|MockObject $expiredQuotesCollection;

    /**
     * @var Delete|MockObject
     */
    private Delete|MockObject $quoteDelete;

    /**
     * @var LoggerInterface|MockObject
     */
    private LoggerInterface|MockObject $logger;

    /**
     * @var CleanExpiredQuotes
     */
    private CleanExpiredQuotes $cron;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->storeManager            = $this->createMock(StoreManagerInterface::class);
        $this->expiredQuotesCollection = $this->createMock(ExpiredQuotesCollection::class);
        $this->quoteDelete            = $this->createMock(Delete::class);
        $this->logger                  = $this->createMock(LoggerInterface::class);

        $this->cron = new CleanExpiredQuotes(
            $this->storeManager,
            $this->expiredQuotesCollection,
            $this->quoteDelete,
            $this->logger,
            self::BATCH_SIZE
        );
    }

    /**
     * Builds a QuoteCollection mock that returns the given entity IDs.
     *
     * @param string[] $ids
     * @return QuoteCollection&MockObject
     */
    private function buildCollectionMock(array $ids): QuoteCollection&MockObject
    {
        $select = $this->createMock(Select::class);
        $select->method('distinct')->willReturnSelf();

        $collection = $this->createMock(QuoteCollection::class);
        $collection->method('addFieldToSelect')->willReturnSelf();
        $collection->method('addFieldToFilter')->willReturnSelf();
        $collection->method('setOrder')->willReturnSelf();
        $collection->method('setPageSize')->willReturnSelf();
        $collection->method('setCurPage')->willReturnSelf();
        $collection->method('getSelect')->willReturn($select);
        $collection->method('getColumnValues')->with('entity_id')->willReturn($ids);

        return $collection;
    }

    /**
     * A single batch smaller than batchSize results in exactly one bulk DELETE
     * containing all IDs, and the loop does not continue.
     */
    public function testExecuteDeletesExpiredQuotesInBulk(): void
    {
        $ids   = ['1', '2'];
        $store = $this->createMock(StoreInterface::class);
        $this->storeManager->method('getStores')->willReturn([$store]);
        $this->expiredQuotesCollection->method('getExpiredQuotes')
            ->willReturn($this->buildCollectionMock($ids));

        $this->quoteDelete->expects($this->once())
            ->method('deleteByIds')
            ->with($ids);

        $this->cron->execute();
    }

    /**
     * When a batch fills the batchSize the loop continues; each full batch and
     * the final partial batch each produce exactly one deleteByIds call.
     */
    public function testExecuteProcessesMultipleBatches(): void
    {
        $firstBatch  = ['1', '2', '3'];  // equals batchSize → loop continues
        $secondBatch = ['4'];            // less than batchSize → loop stops

        $store = $this->createMock(StoreInterface::class);
        $this->storeManager->method('getStores')->willReturn([$store]);
        $this->expiredQuotesCollection->method('getExpiredQuotes')
            ->willReturnOnConsecutiveCalls(
                $this->buildCollectionMock($firstBatch),
                $this->buildCollectionMock($secondBatch)
            );

        $deletedIdSets = [];
        $this->quoteDelete->expects($this->exactly(2))
            ->method('deleteByIds')
            ->willReturnCallback(function ($ids) use (&$deletedIdSets) {
                $deletedIdSets[] = $ids;
            });

        $this->cron->execute();

        $this->assertEquals(['1', '2', '3'], $deletedIdSets[0]);
        $this->assertEquals(['4'], $deletedIdSets[1]);
    }

    /**
     * When a store has no expired quotes deleteByIds is never called.
     */
    public function testExecuteSkipsDeleteWhenNoExpiredQuotes(): void
    {
        $store = $this->createMock(StoreInterface::class);
        $this->storeManager->method('getStores')->willReturn([$store]);
        $this->expiredQuotesCollection->method('getExpiredQuotes')
            ->willReturn($this->buildCollectionMock([]));

        $this->quoteDelete->expects($this->never())->method('deleteByIds');

        $this->cron->execute();
    }

    /**
     * Verifies that the keyset cursor advances to max(ids) after each batch,
     * not merely to the last element as returned by the collection.
     * This guarantees the next batch filter (entity_id > lastProcessedId) is
     * always correct regardless of the order rows come back from MySQL.
     */
    public function testExecuteAdvancesCursorToMaxId(): void
    {
        // Exactly batchSize (3) IDs so the loop continues to a second iteration.
        // Intentionally not in ascending order to prove max() is used, not last element.
        $ids   = ['5', '1', '9'];
        $store = $this->createMock(StoreInterface::class);
        $this->storeManager->method('getStores')->willReturn([$store]);

        $select = $this->createMock(Select::class);
        $select->method('distinct')->willReturnSelf();

        $secondCollection = $this->createMock(QuoteCollection::class);
        $secondCollection->method('addFieldToSelect')->willReturnSelf();
        $secondCollection->method('setOrder')->willReturnSelf();
        $secondCollection->method('setPageSize')->willReturnSelf();
        $secondCollection->method('setCurPage')->willReturnSelf();
        $secondCollection->method('getSelect')->willReturn($select);
        $secondCollection->method('getColumnValues')->with('entity_id')->willReturn([]);

        $capturedCursor = null;
        $secondCollection->method('addFieldToFilter')
            ->willReturnCallback(
                function ($field, $condition) use (&$capturedCursor, $secondCollection) {
                    if ($field === 'main_table.entity_id') {
                        $capturedCursor = $condition;
                    }
                    return $secondCollection;
                }
            );

        $this->expiredQuotesCollection->method('getExpiredQuotes')
            ->willReturnOnConsecutiveCalls(
                $this->buildCollectionMock($ids),
                $secondCollection
            );

        $this->quoteDelete->method('deleteByIds');

        $this->cron->execute();

        // max of ['5','1','9'] is 9; next batch must filter entity_id > 9
        $this->assertEquals(['gt' => 9], $capturedCursor);
    }
}
