<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Order\Payment;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class TransactionRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var TransactionRepository */
    protected $transactionRepository;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Sales\Model\Order\Payment\TransactionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $transactionFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $transactionCollectionFactory;

    /**
     * @var \Magento\Framework\Api\FilterBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filterBuilder;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchCriteriaBuilder;

    protected function setUp()
    {
        $this->transactionFactory = $this->getMock(
            'Magento\Sales\Model\Order\Payment\TransactionFactory',
            [],
            [],
            '',
            false
        );
        $this->transactionCollectionFactory = $this->getMock(
            'Magento\Sales\Model\Resource\Order\Payment\Transaction\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->filterBuilder = $this->getMock('Magento\Framework\Api\FilterBuilder', [], [], '', false);
        $this->searchCriteriaBuilder = $this->getMock(
            'Magento\Framework\Api\SearchCriteriaBuilder',
            [],
            [],
            '',
            false
        );

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->transactionRepository = $this->objectManagerHelper->getObject(
            'Magento\Sales\Model\Order\Payment\TransactionRepository',
            [
                'transactionFactory' => $this->transactionFactory,
                'transactionCollectionFactory' => $this->transactionCollectionFactory,
                'filterBuilder' => $this->filterBuilder,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilder
            ]
        );
    }

    public function testGetIOException()
    {
        $this->setExpectedException('Magento\Framework\Exception\InputException', 'ID required');
        $this->transactionRepository->get(null);
    }

    /**
     * @param int $id
     * @param array $collectionIds
     * @param $conditionType
     * @dataProvider getDataProvider
     */
    public function testGet($id, array $collectionIds, $conditionType)
    {
        $filter = $this->getMock(
            'Magento\Framework\Api\Filter',
            ['getConditionType', 'getField', 'getValue'],
            [],
            '',
            false
        );
        $filter->expects($this->any())->method('getConditionType')->willReturn($conditionType);

        $this->filterBuilder->expects($this->once())->method('setField')->with('transaction_id')->willReturnSelf();
        $this->filterBuilder->expects($this->once())->method('setValue')->with($id)->willReturnSelf();
        $this->filterBuilder->expects($this->once())->method('setConditionType')->with('eq')->willReturnSelf();
        $this->filterBuilder->expects($this->once())->method('create')->willReturn($filter);

        $filterGroup = $this->getMock('Magento\Framework\Api\Search\FilterGroup', [], [], '', false);
        $filterGroup->expects($this->any())
            ->method('getFilters')
            ->willReturn($filter);
        $searchCriteria = $this->getMock('Magento\Framework\Api\SearchCriteria', [], [], '', false);
        $searchCriteria->expects($this->any())
            ->method('getFilterGroups')
            ->willReturn([$filterGroup]);
        $this->searchCriteriaBuilder->expects($this->once())
            ->method('addFilter')
            ->with([$filter]);
        $this->searchCriteriaBuilder->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteria);
        $transactionModelMock = $this->getMockBuilder('Magento\Sales\Model\Order\Payment\Transaction')
            ->disableOriginalConstructor()->setMethods([])->getMock();
        $transactionModelMock->expects($this->any())->method('getId')->will($this->returnValue($id));

        $this->prepareCollection($transactionModelMock, $collectionIds);

        $this->assertSame($transactionModelMock, $this->transactionRepository->get($id));
    }

    public function testFind()
    {
        list($id, $collectionIds, $filterData) = [1, [1], ['field', 'value', 'lteq']];
        $transactionModelMock = $this->getMockBuilder('Magento\Sales\Model\Order\Payment\Transaction')
            ->disableOriginalConstructor()->setMethods([])->getMock();
        $transactionModelMock->expects($this->any())->method('getId')->will($this->returnValue($id));
        $collection = $this->prepareCollection($transactionModelMock, $collectionIds);

        $searchCriteriaMock = $this->getMock('Magento\Framework\Api\SearchCriteria', [], [], '', false);
        $filterGroup = $this->getMock('Magento\Framework\Api\Search\FilterGroup', [], [], '', false);
        $filter = $this->getMock('Magento\Framework\Api\Filter', [], [], '', false);

        $searchCriteriaMock->expects($this->once())->method('getFilterGroups')->will(
            $this->returnValue([$filterGroup])
        );
        $filterGroup->expects($this->once())->method('getFilters')->will($this->returnValue([$filter]));
        $filter->expects($this->once())->method('getField')->will($this->returnValue($filterData[0]));
        $filter->expects($this->once())->method('getValue')->will($this->returnValue($filterData[1]));
        $filter->expects($this->any())->method('getConditionType')->will($this->returnValue($filterData[2]));
        $collection->expects($this->once())->method('addFieldToFilter')->with(
            $filterData[0],
            [$filterData[2] => $filterData[1]]
        );
        $collection->expects($this->once())->method('addPaymentInformation')->with(['method']);
        $collection->expects($this->once())->method('addOrderInformation')->with(['increment_id']);
        $this->assertSame([$id => $transactionModelMock], $this->transactionRepository->find($searchCriteriaMock));
    }

    /**
     * @return array
     */
    public function getDataProvider()
    {
        return [
            [1, [1], 'eq'],
            [1, [], null],
        ];
    }

    /**
     * @param $transactionModelMock
     * @param $collectionIds
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function prepareCollection($transactionModelMock, $collectionIds)
    {
        $collection = $this->getMock(
            'Magento\Sales\Model\Resource\Order\Payment\Transaction\Collection',
            [],
            [],
            '',
            false
        );
        $collection->expects($this->once())->method('getIterator')->will(
            $this->returnValue(new \ArrayIterator([$transactionModelMock]))
        );
        $collection->expects($this->once())->method('getAllIds')->will($this->returnValue($collectionIds));
        $this->transactionCollectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($collection);
        return $collection;
    }
}
