<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Model\Order\Payment\Transaction;


use Magento\Sales\Model\Order\Payment\Transaction;

class RepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchResultFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $filterBuilder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $sortOrderBuilder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $metaData;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityStorage;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $transactionResource;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchCriteria;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $filterGroup;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $filter;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $collection;

    /**
     * @var \Magento\Sales\Model\Order\Payment\Transaction\Repository
     */
    protected $repository;

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->searchResultFactory = $this->getMock(
            'Magento\Sales\Api\Data\TransactionSearchResultInterfaceFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->filterBuilder = $this->getMock(
            'Magento\Framework\Api\FilterBuilder',
            [],
            [],
            '',
            false
        );
        $this->searchCriteriaBuilder = $this->getMock(
            'Magento\Framework\Api\SearchCriteriaBuilder',
            [],
            [],
            '',
            false
        );
        $this->sortOrderBuilder = $this->getMock(
            'Magento\Framework\Api\SortOrderBuilder',
            [],
            [],
            '',
            false
        );
        $this->metaData = $this->getMock(
            'Magento\Sales\Model\ResourceModel\Metadata',
            [],
            [],
            '',
            false
        );
        $entityStorageFactory = $this->getMock(
            'Magento\Sales\Model\EntityStorageFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->entityStorage = $this->getMock(
            'Magento\Sales\Model\EntityStorage',
            [],
            [],
            '',
            false
        );
        $this->transactionResource = $this->getMock(
            'Magento\Sales\Model\ResourceModel\Order\Payment\Transaction',
            [],
            [],
            '',
            false
        );
        $this->searchCriteria = $this->getMock(
            'Magento\Framework\Api\SearchCriteria',
            [],
            [],
            '',
            false
        );
        $this->filterGroup = $this->getMock(
            'Magento\Framework\Api\Search\FilterGroup',
            [],
            [],
            '',
            false
        );
        $this->filter = $this->getMock(
            'Magento\Framework\Api\Filter',
            [],
            [],
            '',
            false
        );
        $this->collection = $this->getMock(
            'Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\Collection',
            [],
            [],
            '',
            false
        );
        $entityStorageFactory->expects($this->once())->method('create')->willReturn($this->entityStorage);
        $this->repository = $objectManager->getObject(
            'Magento\Sales\Model\Order\Payment\Transaction\Repository',
            [
                'searchResultFactory' => $this->searchResultFactory,
                'filterBuilder' => $this->filterBuilder,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilder,
                'sortOrderBuilder' => $this->sortOrderBuilder,
                'metaData' => $this->metaData,
                'entityStorageFactory' => $entityStorageFactory,
            ]
        );
    }

    public function testCreate()
    {
        $expected = "expect";
        $this->metaData->expects($this->once())->method('getNewInstance')->willReturn($expected);
        $this->assertEquals($expected, $this->repository->create());
    }

    public function testSave()
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

    public function testDelete()
    {
        $transactionId = 12;
        $transaction = $this->mockTransaction($transactionId);
        $this->metaData->expects($this->once())->method('getMapper')->willReturn($this->transactionResource);
        $this->transactionResource->expects($this->once())->method('delete')->with($transaction);
        $this->entityStorage->expects($this->once())->method('remove')->with($transactionId);
        $this->assertTrue($this->repository->delete($transaction));
    }

    public function testGet()
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
     * @expectedException \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testGetException()
    {
        $transactionId = null;
        $this->repository->get($transactionId);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testGetNoSuchEntity()
    {
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

    public function testGetExistInStorage()
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

    public function testGetList()
    {
        $field = 'txn_id';
        $value = '33-refund';
        $this->getListMock($field, $value);
        $this->assertSame($this->collection, $this->repository->getList($this->searchCriteria));
    }

    public function testGetByTransactionId()
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

    public function testGetByTransactionIdNotFound()
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
        $this->assertEquals(
            false,
            $this->repository->getByTransactionId($transactionId, $paymentId, $orderId)
        );
    }

    public function testGetByTransactionIdFromStorage()
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

    public function testGetByTransactionType()
    {
        $transactionType = Transaction::TYPE_AUTH;
        $paymentId = 1;
        $orderId = 3;
        $cacheStorage = 'txn_type';
        $identityFieldsForCache = [$transactionType, $paymentId];
        $this->entityStorage->expects($this->once())
            ->method('getByIdentifyingFields')
            ->with($identityFieldsForCache, $cacheStorage)
            ->willReturn(false);
        $this->filterBuilder->expects($this->exactly(2))->method('setField')
            ->withConsecutive(
                [\Magento\Sales\Api\Data\TransactionInterface::TXN_TYPE],
                [\Magento\Sales\Api\Data\TransactionInterface::PAYMENT_ID]
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
        $this->searchCriteriaBuilder->expects($this->once())
            ->method('addFilters')
            ->with([$this->filter, $this->filter])
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
        $this->getListMock(\Magento\Sales\Api\Data\TransactionInterface::TXN_TYPE, $transactionType);
        $transaction = $this->mockTransaction(1, true);
        $this->collection->expects($this->once())->method('getItems')->willReturn([$transaction]);
        $this->entityStorage->expects($this->once())
            ->method('addByIdentifyingFields')
            ->with($transaction, $identityFieldsForCache, $cacheStorage);
        $this->assertEquals(
            $transaction,
            $this->repository->getByTransactionType($transactionType, $paymentId, $orderId)
        );
    }

    public function testGetByTransactionTypeFromCache()
    {
        $transactionType = Transaction::TYPE_AUTH;
        $paymentId = 1;
        $orderId = 3;
        $cacheStorage = 'txn_type';
        $transaction = "transaction";
        $identityFieldsForCache = [$transactionType, $paymentId];
        $this->entityStorage->method('getByIdentifyingFields')
            ->with($identityFieldsForCache, $cacheStorage)
            ->willReturn($transaction);
        $this->assertEquals(
            $transaction,
            $this->repository->getByTransactionType($transactionType, $paymentId, $orderId)
        );
    }

    /**
     * @param string|int|null $transactionId
     * @param bool $withoutTransactionIdMatcher
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function mockTransaction($transactionId, $withoutTransactionIdMatcher = false)
    {
        $transaction = $this->getMock(
            'Magento\Sales\Model\Order\Payment\Transaction',
            [],
            [],
            '',
            false
        );
        if (!$withoutTransactionIdMatcher) {
            $transaction->expects($this->once())->method('getTransactionId')->willReturn($transactionId);
        }
        return $transaction;
    }

    /**
     * @param $field
     * @param $value
     */
    protected function getListMock($field, $value)
    {
        $currentPage = 1;
        $pageSize = 10;
        $this->searchResultFactory->method('create')->willReturn($this->collection);
        $this->searchCriteria->expects($this->once())->method('getFilterGroups')->willReturn([$this->filterGroup]);
        $this->filterGroup->expects($this->once())->method('getFilters')->willReturn([$this->filter]);
        $this->filter->expects($this->once())->method('getConditionType')->willReturn(null);
        $this->filter->expects($this->once())->method('getField')->willReturn($field);
        $this->filter->expects($this->once())->method('getValue')->willReturn($value);
        $this->collection->expects($this->once())->method('addFieldToFilter')->with($field, ['eq' => $value]);
        $this->searchCriteria->expects($this->once())->method('getCurrentPage')->willReturn($currentPage);
        $this->searchCriteria->expects($this->once())->method('getPageSize')->willReturn($pageSize);
        $this->collection->expects($this->once())->method('setCurPage')->with();
        $this->collection->expects($this->once())->method('setPageSize')->with();
        $this->collection->expects($this->once())->method('addPaymentInformation')->with(['method']);
        $this->collection->expects($this->once())->method('addOrderInformation')->with(['increment_id']);
    }
}
