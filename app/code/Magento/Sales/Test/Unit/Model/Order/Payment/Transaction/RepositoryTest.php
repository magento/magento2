<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Model\Order\Payment\Transaction;


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
    protected $orderRepository;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderPaymentRepository;

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
            'Magento\Sales\Model\Resource\Metadata',
            [],
            [],
            '',
            false
        );
        $this->orderPaymentRepository = $this->getMock(
            'Magento\Sales\Model\Order\Payment\Repository',
            [],
            [],
            '',
            false
        );
        $this->orderRepository = $this->getMock(
            'Magento\Sales\Model\OrderRepository',
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
            'Magento\Sales\Model\Resource\Order\Payment\Transaction',
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
            'Magento\Sales\Model\Resource\Order\Payment\Transaction\Collection',
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
                'paymentRepository' => $this->orderPaymentRepository,
                'orderRepository' => $this->orderRepository,
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
        $this->entityStorage->method('has')->with($transactionId)->willReturn(false);
        $this->metaData->expects($this->once())->method('getNewInstance')->willReturn($transaction);
        $this->metaData->expects($this->once())->method('getMapper')->willReturn($this->transactionResource);
        $this->transactionResource->expects($this->once())->method('load')->with($transaction, $transactionId);
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
        $this->entityStorage->method('has')->with($transactionIdFromArgument)->willReturn(false);
        $this->metaData->expects($this->once())->method('getNewInstance')->willReturn($transaction);
        $this->metaData->expects($this->once())->method('getMapper')->willReturn($this->transactionResource);
        $this->transactionResource->expects($this->once())->method('load')->with(
            $transaction,
            $transactionIdFromArgument
        );
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
        $this->assertSame($this->collection, $this->repository->getList($this->searchCriteria));
    }

    /**
     * @param string|int|null $transactionId
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function mockTransaction($transactionId)
    {
        $transaction = $this->getMock(
            'Magento\Sales\Model\Order\Payment\Transaction',
            [],
            [],
            '',
            false
        );
        $transaction->expects($this->once())->method('getTransactionId')->willReturn($transactionId);
        return $transaction;
    }
}
