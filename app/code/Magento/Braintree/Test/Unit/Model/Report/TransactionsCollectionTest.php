<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Model\Report;

use Magento\Braintree\Model\Adapter\BraintreeAdapter;
use Magento\Braintree\Model\Adapter\BraintreeAdapterFactory;
use Magento\Braintree\Model\Report\FilterMapper;
use Magento\Braintree\Model\Report\TransactionsCollection;
use Magento\Framework\Api\Search\DocumentInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class TransactionsCollectionTest
 *
 * Test for class \Magento\Braintree\Model\Report\TransactionsCollection
 */
class TransactionsCollectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var BraintreeAdapter|MockObject
     */
    private $braintreeAdapter;

    /**
     * @var BraintreeAdapterFactory|MockObject
<<<<<<< HEAD
=======
     */
    private $adapterFactoryMock;

    /**
     * @var EntityFactoryInterface|MockObject
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     */
    private $adapterFactory;

    /**
<<<<<<< HEAD
     * @var EntityFactoryInterface|MockObject
=======
     * @var FilterMapper|MockObject
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     */
    private $entityFactory;

    /**
<<<<<<< HEAD
     * @var FilterMapper|MockObject
=======
     * @var DocumentInterface|MockObject
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     */
    private $filterMapper;

    /**
     * @var DocumentInterface|MockObject
     */
    private $transactionMap;

    /**
     * Setup
     */
    protected function setUp()
    {
        $this->transactionMap = $this->getMockBuilder(DocumentInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->entityFactory = $this->getMockBuilder(EntityFactoryInterface::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->filterMapper = $this->getMockBuilder(FilterMapper::class)
            ->setMethods(['getFilter'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->braintreeAdapter = $this->getMockBuilder(BraintreeAdapter::class)
            ->setMethods(['search'])
            ->disableOriginalConstructor()
            ->getMock();
<<<<<<< HEAD
        $this->adapterFactory = $this->getMockBuilder(BraintreeAdapterFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->adapterFactory->method('create')
            ->willReturn($this->braintreeAdapter);
=======
        $this->adapterFactoryMock = $this->getMockBuilder(BraintreeAdapterFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->adapterFactoryMock->method('create')
            ->willReturn($this->braintreeAdapterMock);
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    }

    /**
     * Get items
     */
    public function testGetItems()
    {
        $this->filterMapper->method('getFilter')
            ->willReturn(new BraintreeSearchNodeStub());

        $this->braintreeAdapter->method('search')
            ->willReturn(['transaction1', 'transaction2']);

        $this->entityFactory->expects(self::exactly(2))
            ->method('create')
            ->willReturn($this->transactionMap);

        $collection = new TransactionsCollection(
<<<<<<< HEAD
            $this->entityFactory,
            $this->adapterFactory,
            $this->filterMapper
=======
            $this->entityFactoryMock,
            $this->adapterFactoryMock,
            $this->filterMapperMock
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        );

        $collection->addFieldToFilter('orderId', ['like' => '0']);
        $items = $collection->getItems();
        self::assertEquals(2, count($items));
        self::assertInstanceOf(DocumentInterface::class, $items[1]);
    }

    /**
     * Get empty result
     */
    public function testGetItemsEmptyCollection()
    {
        $this->filterMapper->method('getFilter')
            ->willReturn(new BraintreeSearchNodeStub());

        $this->braintreeAdapter->method('search')
            ->willReturn(null);

        $this->entityFactory->expects(self::never())
            ->method('create')
            ->willReturn($this->transactionMap);

        $collection = new TransactionsCollection(
<<<<<<< HEAD
            $this->entityFactory,
            $this->adapterFactory,
            $this->filterMapper
=======
            $this->entityFactoryMock,
            $this->adapterFactoryMock,
            $this->filterMapperMock
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        );

        $collection->addFieldToFilter('orderId', ['like' => '0']);
        $items = $collection->getItems();
        self::assertEquals(0, count($items));
    }

    /**
     * Get items with limit
     */
    public function testGetItemsWithLimit()
    {
        $transactions = range(1, TransactionsCollection::TRANSACTION_MAXIMUM_COUNT + 10);

        $this->filterMapper->method('getFilter')
            ->willReturn(new BraintreeSearchNodeStub());

<<<<<<< HEAD
        $this->braintreeAdapter->method('search')
            ->willReturn($transations);
=======
        $this->braintreeAdapterMock->expects($this->once())
            ->method('search')
            ->willReturn($transactions);
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc

        $this->entityFactory->expects(self::exactly(TransactionsCollection::TRANSACTION_MAXIMUM_COUNT))
            ->method('create')
            ->willReturn($this->transactionMap);

        $collection = new TransactionsCollection(
<<<<<<< HEAD
            $this->entityFactory,
            $this->adapterFactory,
            $this->filterMapper
=======
            $this->entityFactoryMock,
            $this->adapterFactoryMock,
            $this->filterMapperMock
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        );
        $collection->setPageSize(TransactionsCollection::TRANSACTION_MAXIMUM_COUNT);

        $collection->addFieldToFilter('orderId', ['like' => '0']);
        $items = $collection->getItems();
        self::assertEquals(TransactionsCollection::TRANSACTION_MAXIMUM_COUNT, count($items));
        self::assertInstanceOf(DocumentInterface::class, $items[1]);
    }

    /**
     * Get items with limit
     */
    public function testGetItemsWithNullLimit()
    {
        $transactions = range(1, TransactionsCollection::TRANSACTION_MAXIMUM_COUNT + 10);

        $this->filterMapper->method('getFilter')
            ->willReturn(new BraintreeSearchNodeStub());

<<<<<<< HEAD
        $this->braintreeAdapter->method('search')
            ->willReturn($transations);
=======
        $this->braintreeAdapterMock->expects($this->once())
            ->method('search')
            ->willReturn($transactions);
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc

        $this->entityFactory->expects(self::exactly(TransactionsCollection::TRANSACTION_MAXIMUM_COUNT))
            ->method('create')
            ->willReturn($this->transactionMap);

        $collection = new TransactionsCollection(
<<<<<<< HEAD
            $this->entityFactory,
            $this->adapterFactory,
            $this->filterMapper
=======
            $this->entityFactoryMock,
            $this->adapterFactoryMock,
            $this->filterMapperMock
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        );
        $collection->setPageSize(null);

        $collection->addFieldToFilter('orderId', ['like' => '0']);
        $items = $collection->getItems();
        self::assertEquals(TransactionsCollection::TRANSACTION_MAXIMUM_COUNT, count($items));
        self::assertInstanceOf(DocumentInterface::class, $items[1]);
    }

    /**
     * Add fields to filter
     *
     * @dataProvider addToFilterDataProvider
     */
    public function testAddToFilter($field, $condition, $filterMapperCall, $expectedCondition)
    {
        $this->filterMapper->expects(self::exactly($filterMapperCall))
            ->method('getFilter')
            ->with($field, $expectedCondition)
            ->willReturn(new BraintreeSearchNodeStub());

        $collection = new TransactionsCollection(
<<<<<<< HEAD
            $this->entityFactory,
            $this->adapterFactory,
            $this->filterMapper
=======
            $this->entityFactoryMock,
            $this->adapterFactoryMock,
            $this->filterMapperMock
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        );

        self::assertInstanceOf(
            TransactionsCollection::class,
            $collection->addFieldToFilter($field, $condition)
        );
    }

    /**
     * addToFilter DataProvider
     *
     * @return array
     */
    public function addToFilterDataProvider()
    {
        return [
            ['orderId', ['like' => 1], 1, ['like' => 1]],
            ['type', 'sale', 1, ['eq' => 'sale']],
            [['type', 'orderId'], [], 0, []],
        ];
    }
}
