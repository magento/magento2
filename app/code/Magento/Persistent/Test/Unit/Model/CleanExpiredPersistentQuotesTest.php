<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Persistent\Test\Unit\Model;

use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;
use Magento\Persistent\Model\CleanExpiredPersistentQuotes;
use Magento\Persistent\Model\ResourceModel\ExpiredPersistentQuotesCollectionFactory;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteRepository;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Website;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
 */
class CleanExpiredPersistentQuotesTest extends TestCase
{
    /**
     * @var StoreManagerInterface|MockObject
     */
    private StoreManagerInterface $storeManagerMock;

    /**
     * @var ExpiredPersistentQuotesCollectionFactory|MockObject
     */
    private ExpiredPersistentQuotesCollectionFactory $expiredPersistentQuotesCollectionFactoryMock;

    /**
     * @var QuoteRepository|MockObject
     */
    private QuoteRepository $quoteRepositoryMock;

    /**
     * @var Snapshot|MockObject
     */
    private Snapshot $snapshotMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private LoggerInterface $loggerMock;

    /**
     * @var CleanExpiredPersistentQuotes
     */
    private CleanExpiredPersistentQuotes $cleanExpiredPersistentQuotes;

    protected function setUp(): void
    {
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->expiredPersistentQuotesCollectionFactoryMock = $this->createMock(
            ExpiredPersistentQuotesCollectionFactory::class
        );
        $this->quoteRepositoryMock = $this->createMock(QuoteRepository::class);
        $this->snapshotMock = $this->createMock(Snapshot::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);

        $this->cleanExpiredPersistentQuotes = new CleanExpiredPersistentQuotes(
            $this->storeManagerMock,
            $this->expiredPersistentQuotesCollectionFactoryMock,
            $this->quoteRepositoryMock,
            $this->snapshotMock,
            $this->loggerMock
        );
    }

    /**
     * Set up storeManager to return a single store for the given website.
     *
     * @param int $websiteId
     * @return StoreInterface
     */
    private function mockSingleStoreWebsite(int $websiteId): StoreInterface
    {
        $storeMock = $this->createMock(StoreInterface::class);

        $websiteMock = $this->createMock(Website::class);
        $websiteMock->method('getStores')->willReturn([$storeMock]);

        $this->storeManagerMock->method('getWebsite')
            ->with($websiteId)
            ->willReturn($websiteMock);

        return $storeMock;
    }

    /**
     * Test that all quotes returned by the iterator for a store are deleted.
     *
     * @return void
     * @throws Exception
     */
    public function testExecuteDeletesExpiredQuotes(): void
    {
        $websiteId = 1;
        $storeMock = $this->mockSingleStoreWebsite($websiteId);

        $quoteMock1 = $this->createMock(Quote::class);
        $quoteMock2 = $this->createMock(Quote::class);

        $this->expiredPersistentQuotesCollectionFactoryMock
            ->method('create')
            ->with(['store' => $storeMock])
            ->willReturn(new \ArrayIterator([$quoteMock1, $quoteMock2]));

        $this->quoteRepositoryMock->expects($this->exactly(2))
            ->method('delete')
            ->with($this->logicalOr($quoteMock1, $quoteMock2));
        $this->loggerMock->expects($this->never())->method('error');

        $this->cleanExpiredPersistentQuotes->execute($websiteId);
    }

    /**
     * Test that every processed quote has its version-control snapshot cleared and its
     * instance data released, regardless of whether the delete succeeded or failed.
     *
     * @return void
     * @throws Exception
     */
    public function testExecuteClearsSnapshotAndInstanceForEveryQuote(): void
    {
        $websiteId = 1;
        $storeMock = $this->mockSingleStoreWebsite($websiteId);

        $failingQuoteMock = $this->createMock(Quote::class);
        $failingQuoteMock->method('getId')->willReturn(1);
        $okQuoteMock = $this->createMock(Quote::class);
        $okQuoteMock->method('getId')->willReturn(2);

        $this->expiredPersistentQuotesCollectionFactoryMock
            ->method('create')
            ->willReturn(new \ArrayIterator([$failingQuoteMock, $okQuoteMock]));

        $this->quoteRepositoryMock->method('delete')
            ->willReturnCallback(function ($quote) use ($failingQuoteMock) {
                if ($quote === $failingQuoteMock) {
                    throw new \Exception('delete failed');
                }
            });

        $this->snapshotMock->expects($this->exactly(2))
            ->method('clear')
            ->with($this->logicalOr($failingQuoteMock, $okQuoteMock));
        $failingQuoteMock->expects($this->once())->method('clearInstance');
        $okQuoteMock->expects($this->once())->method('clearInstance');

        $this->cleanExpiredPersistentQuotes->execute($websiteId);
    }

    /**
     * Test that a delete failure is logged and iteration continues to subsequent quotes.
     *
     * @return void
     * @throws Exception
     */
    public function testExecuteContinuesIterationAfterDeleteException(): void
    {
        $websiteId = 1;
        $storeMock = $this->mockSingleStoreWebsite($websiteId);

        $failingQuoteMock = $this->createMock(Quote::class);
        $failingQuoteMock->method('getId')->willReturn(42);
        $okQuoteMock = $this->createMock(Quote::class);
        $okQuoteMock->method('getId')->willReturn(43);

        $this->expiredPersistentQuotesCollectionFactoryMock
            ->method('create')
            ->with(['store' => $storeMock])
            ->willReturn(new \ArrayIterator([$failingQuoteMock, $okQuoteMock]));

        $this->quoteRepositoryMock->method('delete')
            ->willReturnCallback(function ($quote) use ($failingQuoteMock) {
                if ($quote === $failingQuoteMock) {
                    throw new \Exception('delete failed');
                }
            });

        $this->quoteRepositoryMock->expects($this->exactly(2))->method('delete');
        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with($this->stringContains('ID: 42'));

        $this->cleanExpiredPersistentQuotes->execute($websiteId);
    }

    /**
     * Test that an empty iterator results in no delete/logger calls.
     *
     * @return void
     * @throws Exception
     */
    public function testExecuteHandlesEmptyIteration(): void
    {
        $websiteId = 1;
        $storeMock = $this->mockSingleStoreWebsite($websiteId);

        $this->expiredPersistentQuotesCollectionFactoryMock
            ->method('create')
            ->with(['store' => $storeMock])
            ->willReturn(new \ArrayIterator([]));

        $this->quoteRepositoryMock->expects($this->never())->method('delete');
        $this->loggerMock->expects($this->never())->method('error');
        $this->snapshotMock->expects($this->never())->method('clear');

        $this->cleanExpiredPersistentQuotes->execute($websiteId);
    }

    /**
     * Test that every store in the website is processed via its own factory-created iterator.
     *
     * @return void
     * @throws Exception
     */
    public function testExecuteLoopsOverAllStoresInWebsite(): void
    {
        $websiteId = 1;

        $storeMock1 = $this->createMock(StoreInterface::class);
        $storeMock2 = $this->createMock(StoreInterface::class);

        $websiteMock = $this->createMock(Website::class);
        $websiteMock->method('getStores')->willReturn([$storeMock1, $storeMock2]);

        $this->storeManagerMock->method('getWebsite')
            ->with($websiteId)
            ->willReturn($websiteMock);

        $quoteMockStore1 = $this->createMock(Quote::class);
        $quoteMockStore2 = $this->createMock(Quote::class);

        $this->expiredPersistentQuotesCollectionFactoryMock
            ->expects($this->exactly(2))
            ->method('create')
            ->willReturnCallback(
                function (array $data) use ($storeMock1, $storeMock2, $quoteMockStore1, $quoteMockStore2) {
                    return match ($data['store']) {
                        $storeMock1 => new \ArrayIterator([$quoteMockStore1]),
                        $storeMock2 => new \ArrayIterator([$quoteMockStore2]),
                    };
                }
            );

        $this->quoteRepositoryMock->expects($this->exactly(2))
            ->method('delete')
            ->with($this->logicalOr($quoteMockStore1, $quoteMockStore2));

        $this->cleanExpiredPersistentQuotes->execute($websiteId);
    }
}
