<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order\Payment\Transaction;

use Magento\Framework\Api\Filter;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Api\Data\TransactionSearchResultInterfaceFactory;
use Magento\Sales\Model\EntityStorage;
use Magento\Sales\Model\EntityStorageFactory;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Sales\Model\Order\Payment\Transaction\Repository;
use Magento\Sales\Model\ResourceModel\Metadata;
use Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RepositoryTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $searchResultFactory;

    /**
     * @var MockObject
     */
    protected $filterBuilder;

    /**
     * @var MockObject
     */
    protected $searchCriteriaBuilder;

    /**
     * @var MockObject
     */
    protected $sortOrderBuilder;

    /**
     * @var MockObject
     */
    protected $metaData;

    /**
     * @var MockObject
     */
    protected $entityStorage;

    /**
     * @var MockObject
     */
    protected $transactionResource;

    /**
     * @var MockObject
     */
    protected $searchCriteria;

    /**
     * @var MockObject
     */
    protected $filterGroup;

    /**
     * @var MockObject
     */
    protected $filter;

    /**
     * @var MockObject
     */
    protected $collection;

    /**
     * @var Repository
     */
    protected $repository;

    /**
     * @var MockObject
     */
    private $collectionProcessor;

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->searchResultFactory = $this->createPartialMock(
            TransactionSearchResultInterfaceFactory::class,
            ['create']
        );
        $this->filterBuilder = $this->createMock(FilterBuilder::class);
        $this->searchCriteriaBuilder = $this->createMock(SearchCriteriaBuilder::class);
        $this->sortOrderBuilder = $this->createMock(SortOrderBuilder::class);
        $this->metaData = $this->createMock(Metadata::class);
        $entityStorageFactory = $this->createPartialMock(EntityStorageFactory::class, ['create']);
        $this->entityStorage = $this->createMock(EntityStorage::class);
        $this->transactionResource = $this->createMock(
            \Magento\Sales\Model\ResourceModel\Order\Payment\Transaction::class
        );
        $this->searchCriteria = $this->createMock(SearchCriteria::class);
        $this->filterGroup = $this->createMock(FilterGroup::class);
        $this->filter = $this->createMock(Filter::class);
        $this->collection = $this->createMock(
            Collection::class
        );
        $entityStorageFactory->expects($this->once())->method('create')->willReturn($this->entityStorage);
        $this->collectionProcessor = $this->createMock(
            CollectionProcessorInterface::class
        );
        $this->repository = $objectManager->getObject(
            Repository::class,
            [
                'searchResultFactory' => $this->searchResultFactory,
                'filterBuilder' => $this->filterBuilder,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilder,
                'sortOrderBuilder' => $this->sortOrderBuilder,
                'metaData' => $this->metaData,
                'entityStorageFactory' => $entityStorageFactory,
                'collectionProcessor' => $this->collectionProcessor
            ]
        );
    }

    /**
     * @return void
     */
    public function testCreate(): void
    {
        $expected = "expect";
        $this->metaData->expects($this->once())->method('getNewInstance')->willReturn($expected);
        $this->assertEquals($expected, $this->repository->create());
    }

    /**
     * @return void
     */
    public function testSave(): void
    {
        $transactionId = 12;
        $transaction = $this->mockTransaction($transactionId);
        $this->metaData->expects($this->once())->method('getMapper')->willReturn($this->transactionResource);
        $this->transactionResource->expects($this->once())->method('save')
            ->with($transaction)
            ->willReturn($transaction);
        $this->entityStorage->expects($this->once())->method('add')->with($transaction);
        $this->entityStorage->expects($this->once())->method('get')->with($transactionId)->willReturn($transaction);
        $this->assertSame($transaction, $this->repository->save($transaction));
    }

    /**
     * @return void
     */
    public function testDelete(): void
    {
        $transactionId = 12;
        $transaction = $this->mockTransaction($transactionId);
        $this->metaData->expects($this->once())->method('getMapper')->willReturn($this->transactionResource);
        $this->transactionResource->expects($this->once())->method('delete')->with($transaction);
        $this->entityStorage->expects($this->once())->method('remove')->with($transactionId);
        $this->assertTrue($this->repository->delete($transaction));
    }

    /**
     * @return void
     */
    public function testGet(): void
    {
        $transactionId = 12;
        $transaction = $this->mockTransaction($transactionId);
        $transaction->expects($this->any())->method('load')->with($transactionId)->willReturn($transaction);
        $this->entityStorage->method('has')->with($transactionId)->willReturn(false);
        $this->metaData->expects($this->once())->method('getNewInstance')->willReturn($transaction);
        $this->entityStorage->expects($this->once())->method('add')->with($transaction);
        $this->entityStorage->expects($this->once())->method('get')->with($transactionId)->willReturn($transaction);
        $this->assertSame($transaction, $this->repository->get($transactionId));
    }

    /**
     * @return void
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function testGetException(): void
    {
        $this->expectException('Magento\Framework\Exception\InputException');
        $transactionId = null;
        $this->repository->get($transactionId);
    }

    /**
     * @return void
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function testGetNoSuchEntity(): void
    {
        $this->expectException('Magento\Framework\Exception\NoSuchEntityException');
        $transactionId = null;
        $transactionIdFromArgument = 12;
        $transaction = $this->mockTransaction($transactionId);
        $transaction->expects($this->any())->method('load')->with(12)->willReturn($transaction);
        $this->entityStorage->method('has')->with($transactionIdFromArgument)->willReturn(false);
        $this->metaData->expects($this->once())->method('getNewInstance')->willReturn($transaction);
        $this->entityStorage->expects($this->never())->method('add');
        $this->entityStorage->expects($this->never())->method('get');
        $this->assertSame($transaction, $this->repository->get(12));
    }

    /**
     * @return void
     */
    public function testGetExistInStorage(): void
    {
        $transactionId = 12;
        $transaction = "transaction";
        $this->entityStorage->method('has')->with($transactionId)->willReturn(true);
        $this->metaData->expects($this->never())->method('getNewInstance')->willReturn($transaction);
        $this->metaData->expects($this->never())->method('getMapper')->willReturn($this->transactionResource);
        $this->transactionResource->expects($this->never())->method('load')->with($transaction, $transactionId);
        $this->entityStorage->expects($this->never())->method('add')->with($transaction);
        $this->entityStorage->expects($this->once())->method('get')->with($transactionId)->willReturn($transaction);
        $this->assertSame($transaction, $this->repository->get($transactionId));
    }

    /**
     * @return void
     */
    public function testGetList(): void
    {
        $this->initListMock();
        $this->collectionProcessor->expects($this->once())
            ->method('process')
            ->with($this->searchCriteria, $this->collection);
        $this->assertSame($this->collection, $this->repository->getList($this->searchCriteria));
    }

    /**
     * @return void
     */
    public function testGetByTransactionId(): void
    {
        $transactionId = "100-refund";
        $paymentId = 1;
        $orderId = 3;
        $cacheStorage = 'txn_id';
        $transaction = $this->mockTransaction($transactionId, true);
        $identityFieldsForCache = [$transactionId, $paymentId, $orderId];
        $this->entityStorage->method('getByIdentifyingFields')
            ->with($identityFieldsForCache, $cacheStorage)
            ->willReturn(false);
        $this->metaData->expects($this->once())->method('getMapper')->willReturn($this->transactionResource);
        $this->metaData->expects($this->once())->method('getNewInstance')->willReturn($transaction);
        $this->transactionResource->expects($this->once())->method('loadObjectByTxnId')->with(
            $transaction,
            $orderId,
            $paymentId,
            $transactionId
        )->willReturn($transaction);
        $transaction->expects($this->once())->method('getId')->willReturn($transactionId);
        $this->entityStorage->expects($this->once())
            ->method('addByIdentifyingFields')
            ->with($transaction, $identityFieldsForCache, $cacheStorage);
        $this->assertEquals($transaction, $this->repository->getByTransactionId($transactionId, $paymentId, $orderId));
    }

    /**
     * @return void
     */
    public function testGetByTransactionIdNotFound(): void
    {
        $transactionId = "100-refund";
        $paymentId = 1;
        $orderId = 3;
        $cacheStorage = 'txn_id';
        $transaction = $this->mockTransaction($transactionId, true);
        $identityFieldsForCache = [$transactionId, $paymentId, $orderId];
        $this->entityStorage->method('getByIdentifyingFields')
            ->with($identityFieldsForCache, $cacheStorage)
            ->willReturn(false);
        $this->metaData->expects($this->once())->method('getMapper')->willReturn($this->transactionResource);
        $this->metaData->expects($this->once())->method('getNewInstance')->willReturn($transaction);
        $this->transactionResource->expects($this->once())->method('loadObjectByTxnId')->with(
            $transaction,
            $orderId,
            $paymentId,
            $transactionId
        )->willReturn(false);
        $transaction->expects($this->once())->method('getId')->willReturn(false);
        $this->assertFalse(
            $this->repository->getByTransactionId($transactionId, $paymentId, $orderId)
        );
    }

    /**
     * @return void
     */
    public function testGetByTransactionIdFromStorage(): void
    {
        $transactionId = "100-refund";
        $paymentId = 1;
        $orderId = 3;
        $cacheStorage = 'txn_id';
        $transaction = "transaction";
        $identityFieldsForCache = [$transactionId, $paymentId, $orderId];
        $this->entityStorage->method('getByIdentifyingFields')
            ->with($identityFieldsForCache, $cacheStorage)
            ->willReturn($transaction);
        $this->assertEquals(
            $transaction,
            $this->repository->getByTransactionId($transactionId, $paymentId, $orderId)
        );
    }

    /**
     * @return void
     */
    public function testGetByTransactionType(): void
    {
        $transactionType = Transaction::TYPE_AUTH;
        $paymentId = 1;
        $cacheStorage = 'txn_type';
        $identityFieldsForCache = [$transactionType, $paymentId];
        $this->entityStorage->expects($this->once())
            ->method('getByIdentifyingFields')
            ->with($identityFieldsForCache, $cacheStorage)
            ->willReturn(false);
        $this->filterBuilder->expects($this->exactly(2))->method('setField')
            ->withConsecutive(
                [TransactionInterface::TXN_TYPE],
                [TransactionInterface::PAYMENT_ID]
            )->willReturnSelf();
        $this->filterBuilder->expects($this->exactly(2))->method('setValue')
            ->withConsecutive(
                [$transactionType],
                [$paymentId]
            )->willReturnSelf();
        $this->filterBuilder->expects($this->exactly(2))->method('create')->willReturn($this->filter);

        $transactionIdSort = "TransactionIdSort";
        $createdAtSort = "createdAtSort";
        $this->sortOrderBuilder->expects($this->exactly(2))->method('setField')
            ->withConsecutive(
                ['transaction_id'],
                ['created_at']
            )->willReturnSelf();
        $this->sortOrderBuilder->expects($this->exactly(2))->method('setDirection')
            ->with(\Magento\Framework\Data\Collection::SORT_ORDER_DESC)->willReturnSelf();
        $this->sortOrderBuilder->expects($this->exactly(2))->method('create')->willReturnOnConsecutiveCalls(
            $transactionIdSort,
            $createdAtSort
        );
        $this->searchCriteriaBuilder->expects($this->exactly(2))
            ->method('addFilters')
            ->with([$this->filter])
            ->willReturnSelf();
        $this->searchCriteriaBuilder->expects($this->exactly(2))
            ->method('addSortOrder')
            ->withConsecutive(
                [$transactionIdSort],
                [$createdAtSort]
            )->willReturnSelf();
        $this->searchCriteriaBuilder->expects($this->once())
            ->method('create')
            ->willReturn($this->searchCriteria);
        $this->initListMock();
        $transaction = $this->mockTransaction(1, true);
        $this->collection->expects($this->once())->method('getItems')->willReturn([$transaction]);
        $this->entityStorage->expects($this->once())
            ->method('addByIdentifyingFields')
            ->with($transaction, $identityFieldsForCache, $cacheStorage);
        $this->assertEquals(
            $transaction,
            $this->repository->getByTransactionType($transactionType, $paymentId)
        );
    }

    /**
     * @return void
     */
    public function testGetByTransactionTypeFromCache(): void
    {
        $transactionType = Transaction::TYPE_AUTH;
        $paymentId = 1;
        $cacheStorage = 'txn_type';
        $transaction = "transaction";
        $identityFieldsForCache = [$transactionType, $paymentId];
        $this->entityStorage->method('getByIdentifyingFields')
            ->with($identityFieldsForCache, $cacheStorage)
            ->willReturn($transaction);
        $this->assertEquals(
            $transaction,
            $this->repository->getByTransactionType($transactionType, $paymentId)
        );
    }

    /**
     * @param string|int|null $transactionId
     * @param bool $withoutTransactionIdMatcher
     * @return MockObject
     */
    protected function mockTransaction($transactionId, $withoutTransactionIdMatcher = false)
    {
        $transaction = $this->createMock(Transaction::class);
        if (!$withoutTransactionIdMatcher) {
            $transaction->expects($this->once())->method('getTransactionId')->willReturn($transactionId);
        }
        return $transaction;
    }

    /**
     * @return void
     */
    protected function initListMock(): void
    {
        $this->searchResultFactory->method('create')->willReturn($this->collection);
        $this->collection->expects($this->once())->method('addPaymentInformation')->with(['method']);
        $this->collection->expects($this->once())->method('addOrderInformation')->with(['increment_id']);
    }
}
