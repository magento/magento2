<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model;

use Magento\Sales\Model\OrderRepository;
use Magento\Sales\Model\ResourceModel\Metadata;
use Magento\Sales\Api\Data\OrderSearchResultInterfaceFactory as SearchResultFactory;
use Magento\Framework\Api\SortOrder;

/**
 * Class OrderRepositoryTest
 */
class OrderRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\OrderRepository
     */
    protected $model;

    /**
     * @var Metadata|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $metadata;

    /**
     * @var SearchResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchResultFactory;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * Setup the test
     */
    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $className = 'Magento\Sales\Model\ResourceModel\Metadata';
        $this->metadata = $this->getMock($className, [], [], '', false);

        $className = 'Magento\Sales\Api\Data\OrderSearchResultInterfaceFactory';
        $this->searchResultFactory = $this->getMock($className, ['create'], [], '', false);

        $this->model = $this->objectManager->getObject(
            '\Magento\Sales\Model\OrderRepository',
            [
                'metadata' => $this->metadata,
                'searchResultFactory' => $this->searchResultFactory,
            ]
        );
    }

    /**
     * TODO: Cover with unit tests the other methods in the repository
     * test GetList
     */
    public function testGetList()
    {
        $fieldName = 'field';
        $searchCriteriaMock = $this->getMock('Magento\Framework\Api\SearchCriteria', [], [], '', false);

        $collectionMock = $this->getMock('Magento\Sales\Model\ResourceModel\Order\Collection', [], [], '', false);

        $filterGroupMock = $this->getMock('\Magento\Framework\Api\Search\FilterGroup', [], [], '', false);
        $filterGroupFilterMock = $this->getMock('\Magento\Framework\Api\Filter', [], [], '', false);
        $sortOrderMock = $this->getMock('\Magento\Framework\Api\SortOrder', [], [], '', false);
        $itemsMock = $this->getMock('Magento\Sales\Model\Order', [], [], '', false);

        $extensionAttributes = $this->getMock(
            '\Magento\Sales\Api\Data\OrderExtension',
            ['getShippingAssignments'],
            [],
            '',
            false
        );
        $shippingAssignmentBuilder = $this->getMock(
            '\Magento\Sales\Model\Order\ShippingAssignmentBuilder',
            [],
            [],
            '',
            false
        );

        $itemsMock->expects($this->once())->method('getExtensionAttributes')->willReturn($extensionAttributes);
        $extensionAttributes->expects($this->any())
            ->method('getShippingAssignments')
            ->willReturn($shippingAssignmentBuilder);

        $this->searchResultFactory->expects($this->once())->method('create')->willReturn($collectionMock);

        $searchCriteriaMock->expects($this->once())->method('getFilterGroups')->willReturn([$filterGroupMock]);
        $filterGroupMock->expects($this->once())->method('getFilters')->willReturn([$filterGroupFilterMock]);
        $filterGroupFilterMock->expects($this->exactly(2))->method('getConditionType')->willReturn('eq');
        $filterGroupFilterMock->expects($this->atLeastOnce())->method('getField')->willReturn($fieldName);
        $filterGroupFilterMock->expects($this->once())->method('getValue')->willReturn('value');
        $sortOrderMock->expects($this->once())->method('getDirection');
        $searchCriteriaMock->expects($this->once())->method('getSortOrders')->willReturn([$sortOrderMock]);
        $sortOrderMock->expects($this->atLeastOnce())->method('getField')->willReturn($fieldName);
        $collectionMock->expects($this->once())->method('addFieldToFilter')
            ->willReturn(SortOrder::SORT_ASC);
        $collectionMock->expects($this->once())->method('addOrder')->with($fieldName, 'DESC');
        $searchCriteriaMock->expects($this->once())->method('getCurrentPage')->willReturn(4);
        $collectionMock->expects($this->once())->method('setCurPage')->with(4);
        $searchCriteriaMock->expects($this->once())->method('getPageSize')->willReturn(42);
        $collectionMock->expects($this->once())->method('setPageSize')->with(42);
        $collectionMock->expects($this->once())->method('getItems')->willReturn([$itemsMock]);

        $this->assertEquals($collectionMock, $this->model->getList($searchCriteriaMock));
    }
}
