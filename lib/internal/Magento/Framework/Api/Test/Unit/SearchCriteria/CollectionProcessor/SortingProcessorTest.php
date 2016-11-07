<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api\Test\Unit\SearchCriteria\CollectionProcessor;

use Magento\Framework\Api\SearchCriteria\CollectionProcessor\SortingProcessor;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Data\Collection;
use Magento\Framework\Data\Collection\AbstractDb;

class SortingProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Return model
     *
     * @param array $fieldMapping
     * @param array $defaultOrders
     * @return SortingProcessor
     */
    private function getModel(array $fieldMapping, array $defaultOrders)
    {
        return new SortingProcessor($fieldMapping, $defaultOrders);
    }

    public function testProcess()
    {
        $orderOneField = 'orderOneField';
        $orderOneFieldMapped = 'orderOneFieldMapped';
        $orderOneDirection = SortOrder::SORT_ASC;

        $orderTwoField = 'orderTwoField';
        $orderTwoDirection = SortOrder::SORT_DESC;

        $orderThreeField = 'orderTwoField';
        $orderThreeDirection = '!!@!@';

        $fieldMapping = [$orderOneField => $orderOneFieldMapped];

        $defaultOrders = ['orderTwoField' => 'DESC'];

        $model = $this->getModel($fieldMapping, $defaultOrders);

        /** @var SortOrder|\PHPUnit_Framework_MockObject_MockObject $sortOrderOneMock */
        $sortOrderOneMock = $this->getMockBuilder(SortOrder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $sortOrderOneMock->expects($this->once())
            ->method('getField')
            ->willReturn($orderOneField);
        $sortOrderOneMock->expects($this->once())
            ->method('getDirection')
            ->willReturn($orderOneDirection);

        /** @var SortOrder|\PHPUnit_Framework_MockObject_MockObject $sortOrderTwoMock */
        $sortOrderTwoMock = $this->getMockBuilder(SortOrder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $sortOrderTwoMock->expects($this->once())
            ->method('getField')
            ->willReturn($orderTwoField);
        $sortOrderTwoMock->expects($this->once())
            ->method('getDirection')
            ->willReturn($orderTwoDirection);

        /** @var SortOrder|\PHPUnit_Framework_MockObject_MockObject $sortOrderThreeMock */
        $sortOrderThreeMock = $this->getMockBuilder(SortOrder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $sortOrderThreeMock->expects($this->once())
            ->method('getField')
            ->willReturn($orderThreeField);
        $sortOrderThreeMock->expects($this->once())
            ->method('getDirection')
            ->willReturn($orderThreeDirection);

        /** @var SearchCriteriaInterface|\PHPUnit_Framework_MockObject_MockObject $searchCriteriaMock */
        $searchCriteriaMock = $this->getMockBuilder(SearchCriteriaInterface::class)
            ->getMock();

        $searchCriteriaMock->expects($this->exactly(2))
            ->method('getSortOrders')
            ->willReturn([$sortOrderOneMock, $sortOrderTwoMock, $sortOrderThreeMock]);

        /** @var AbstractDb|\PHPUnit_Framework_MockObject_MockObject $searchCriteriarMock */
        $collectionMock = $this->getMockBuilder(AbstractDb::class)
            ->disableOriginalConstructor()
            ->getMock();

        $collectionMock->expects($this->exactly(3))
            ->method('addOrder')
            ->withConsecutive(
                [$orderOneFieldMapped, $orderOneDirection],
                [$orderTwoField, $orderTwoDirection],
                [$orderThreeField, Collection::SORT_ORDER_DESC]
            )->willReturnSelf();

        $model->process($searchCriteriaMock, $collectionMock);
    }

    public function testProcessWithDefaults()
    {
        $defaultOneField = 'defaultOneField';
        $defaultOneFieldMapped = 'defaultOneFieldMapped';
        $defaultOneDirection = SortOrder::SORT_ASC;

        $defaultTwoField = 'defaultTwoField';
        $defaultTwoDirection = SortOrder::SORT_DESC;

        $defaultThreeField = 'defaultThreeField';
        $defaultThreeDirection = '$#%^';

        $fieldMapping = [$defaultOneField => $defaultOneFieldMapped];

        $defaultOrders = [
            $defaultOneField => $defaultOneDirection,
            $defaultTwoField => $defaultTwoDirection,
            $defaultThreeField => $defaultThreeDirection,
        ];

        $model = $this->getModel($fieldMapping, $defaultOrders);

        /** @var SearchCriteriaInterface|\PHPUnit_Framework_MockObject_MockObject $searchCriteriaMock */
        $searchCriteriaMock = $this->getMockBuilder(SearchCriteriaInterface::class)
            ->getMock();

        $searchCriteriaMock->expects($this->once())
            ->method('getSortOrders')
            ->willReturn([]);

        /** @var AbstractDb|\PHPUnit_Framework_MockObject_MockObject $searchCriteriarMock */
        $collectionMock = $this->getMockBuilder(AbstractDb::class)
            ->disableOriginalConstructor()
            ->getMock();

        $collectionMock->expects($this->exactly(3))
            ->method('addOrder')
            ->withConsecutive(
                [$defaultOneFieldMapped, $defaultOneDirection],
                [$defaultTwoField, $defaultTwoDirection],
                [$defaultThreeField, Collection::SORT_ORDER_DESC]
            )->willReturnSelf();

        $model->process($searchCriteriaMock, $collectionMock);
    }
}
