<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Persistent\Test\Unit\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;
use Magento\Persistent\Model\CleanExpiredPersistentQuotes;
use Magento\Persistent\Model\ResourceModel\ExpiredPersistentQuotesCollection;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteRepository;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Quote\Model\ResourceModel\Quote\Collection;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Website;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CleanExpiredPersistentQuotesTest extends TestCase
{
    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManagerMock;

    /**
     * @var ExpiredPersistentQuotesCollection
     */
    private ExpiredPersistentQuotesCollection $expiredPersistentQuotesCollectionMock;

    /**
     * @var QuoteRepository
     */
    private QuoteRepository $quoteRepositoryMock;

    /**
     * @var MockObject|Snapshot
     */
    private MockObject|Snapshot $snapshotMock;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $loggerMock;

    /**
     * @var CleanExpiredPersistentQuotes
     */
    private CleanExpiredPersistentQuotes $cleanExpiredPersistentQuotes;

    /**
     * @var int
     */
    private int $batchSize;

    protected function setUp(): void
    {
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->expiredPersistentQuotesCollectionMock = $this->createMock(ExpiredPersistentQuotesCollection::class);
        $this->quoteRepositoryMock = $this->createMock(QuoteRepository::class);
        $this->snapshotMock = $this->createMock(Snapshot::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->batchSize = 500;

        $this->cleanExpiredPersistentQuotes = new CleanExpiredPersistentQuotes(
            $this->storeManagerMock,
            $this->expiredPersistentQuotesCollectionMock,
            $this->quoteRepositoryMock,
            $this->snapshotMock,
            $this->loggerMock,
            $this->batchSize
        );
    }

    /**
     * Test execute method
     *
     * @return void
     * @throws LocalizedException
     * @throws Exception
     */
    public function testExecuteDeletesExpiredQuotes(): void
    {
        $websiteId = 1;

        $storeMock = $this->createMock(StoreInterface::class);
        $storeMock->method('getId')->willReturn(1);
        $storeMock->method('getWebsiteId')->willReturn(2);

        $websiteMock = $this->createMock(Website::class);
        $websiteMock->method('getStores')->willReturn([$storeMock]);

        $this->storeManagerMock->method('getWebsite')
            ->with($websiteId)
            ->willReturn($websiteMock);

        $quoteCollectionMock = $this->createMock(Collection::class);
        $quoteCollectionMock->method('getSize')->willReturn(1);  // Simulate that we have expired quotes
        $quoteCollectionMock->method('getLastPageNumber')->willReturn(1);
        $quoteCollectionMock->method('setPageSize')->willReturnSelf();
        $quoteCollectionMock->method('setCurPage')->willReturnSelf();
        $quoteCollectionMock->expects($this->exactly(2))
            ->method('count')
            ->willReturnCallback(function () {
                $count = 999;
                static $filterCallCount = 0;
                $filterCallCount++;

                match ($filterCallCount) {
                    1 => $count = 1,
                    2 => $count = 0
                };

                return $count;
            });

        $this->expiredPersistentQuotesCollectionMock
            ->method('getExpiredPersistentQuotes')
            ->with($storeMock)
            ->willReturn($quoteCollectionMock);

        $quoteMock = $this->createMock(Quote::class);
        $quoteCollectionMock->method('getIterator')->willReturn(new \ArrayIterator([$quoteMock]));

        $this->quoteRepositoryMock->expects($this->once())->method('delete');

        $this->cleanExpiredPersistentQuotes->execute($websiteId);
    }
}
