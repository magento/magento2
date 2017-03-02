<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api\Test\Unit\SearchCriteria\CollectionProcessor;

use Magento\Framework\Api\Filter;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor\JoinProcessor;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor\JoinProcessor\CustomJoinInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Data\Collection\AbstractDb;

class JoinProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Return model
     *
     * @param CustomJoinInterface[] $customJoins
     * @param array $fieldMapping
     * @return JoinProcessor
     */
    private function getModel(array $customJoins, array $fieldMapping)
    {
        return new JoinProcessor($customJoins, $fieldMapping);
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testProcess()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject $customJoinMock */
        $customJoinMock = $this->getMock(
            \Magento\Framework\Api\SearchCriteria\CollectionProcessor\JoinProcessor\CustomJoinInterface::class
        );

        $customField = 'customJoinField';
        $joins = [$customField => $customJoinMock];
        $fieldMapping = [
            'customJoinFieldAzaza' => 'customJoinField'
        ];

        $model = $this->getModel($joins, $fieldMapping);

        /** @var SearchCriteriaInterface|\PHPUnit_Framework_MockObject_MockObject $searchCriteriaMock */
        $searchCriteriaMock = $this->getMockBuilder(SearchCriteriaInterface::class)
            ->getMock();

        /** @var FilterGroup |\PHPUnit_Framework_MockObject_MockObject $JoinGroupOneMock */
        $filterGroup = $this->getMockBuilder(FilterGroup::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var Filter |\PHPUnit_Framework_MockObject_MockObject $JoinThreeMock */
        $filter1 = $this->getMockBuilder(Filter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $filter1->expects($this->atLeastOnce())
            ->method('getField')
            ->willReturn('customJoinFieldAzaza');
        $filter2 = $this->getMockBuilder(Filter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $filter2->expects($this->atLeastOnce())
            ->method('getField')
            ->willReturn('someOtherField');
        $filterGroup->expects($this->once())
            ->method('getFilters')
            ->willReturn([$filter1, $filter2]);

        $searchCriteriaMock->expects($this->exactly(2))
            ->method('getFilterGroups')
            ->willReturn([$filterGroup]);

        /** @var AbstractDb|\PHPUnit_Framework_MockObject_MockObject $searchCriteriarMock */
        $collectionMock = $this->getMockBuilder(AbstractDb::class)
            ->disableOriginalConstructor()
            ->getMock();

        $customJoinMock->expects($this->once())
            ->method('apply')
            ->with($collectionMock)
            ->willReturn(true);

        $model->process($searchCriteriaMock, $collectionMock);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testProcessWithException()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject $customJoinMock */
        $customJoinMock = $this->getMockBuilder(\stdClass::class)
            ->getMock();

        $customField = 'customJoinField';
        $joins = [$customField => $customJoinMock];

        /** @var SearchCriteriaInterface|\PHPUnit_Framework_MockObject_MockObject $searchCriteriaMock */
        $searchCriteriaMock = $this->getMockBuilder(SearchCriteriaInterface::class)
            ->getMock();

        $model = $this->getModel($joins, []);
        /** @var SortOrder |\PHPUnit_Framework_MockObject_MockObject $JoinGroupOneMock */
        $sortOrder = $this->getMockBuilder(SortOrder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $sortOrder->expects($this->atLeastOnce())
            ->method('getField')
            ->willReturn('customJoinField');
        $searchCriteriaMock->expects($this->exactly(2))
            ->method('getSortOrders')
            ->willReturn([$sortOrder]);
        /** @var AbstractDb|\PHPUnit_Framework_MockObject_MockObject $searchCriteriarMock */
        $collectionMock = $this->getMockBuilder(AbstractDb::class)
            ->disableOriginalConstructor()
            ->getMock();

        $model->process($searchCriteriaMock, $collectionMock);
    }
}
