<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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

class JoinProcessorTest extends \PHPUnit\Framework\TestCase
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
        /** @var \PHPUnit\Framework\MockObject\MockObject $customJoinMock */
        $customJoinMock = $this->createMock(
            \Magento\Framework\Api\SearchCriteria\CollectionProcessor\JoinProcessor\CustomJoinInterface::class
        );

        $customField = 'customJoinField';
        $joins = [$customField => $customJoinMock];
        $fieldMapping = [
            'customJoinFieldAzaza' => 'customJoinField'
        ];

        $model = $this->getModel($joins, $fieldMapping);

        /** @var SearchCriteriaInterface|\PHPUnit\Framework\MockObject\MockObject $searchCriteriaMock */
        $searchCriteriaMock = $this->getMockBuilder(SearchCriteriaInterface::class)
            ->getMock();

        /** @var FilterGroup |\PHPUnit\Framework\MockObject\MockObject $JoinGroupOneMock */
        $filterGroup = $this->getMockBuilder(FilterGroup::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var Filter |\PHPUnit\Framework\MockObject\MockObject $JoinThreeMock */
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

        /** @var AbstractDb|\PHPUnit\Framework\MockObject\MockObject $searchCriteriarMock */
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
     */
    public function testProcessWithException()
    {
        $this->expectException(\InvalidArgumentException::class);

        /** @var \PHPUnit\Framework\MockObject\MockObject $customJoinMock */
        $customJoinMock = $this->getMockBuilder(\stdClass::class)
            ->getMock();

        $customField = 'customJoinField';
        $joins = [$customField => $customJoinMock];

        /** @var SearchCriteriaInterface|\PHPUnit\Framework\MockObject\MockObject $searchCriteriaMock */
        $searchCriteriaMock = $this->getMockBuilder(SearchCriteriaInterface::class)
            ->getMock();

        $model = $this->getModel($joins, []);
        /** @var SortOrder |\PHPUnit\Framework\MockObject\MockObject $JoinGroupOneMock */
        $sortOrder = $this->getMockBuilder(SortOrder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $sortOrder->expects($this->atLeastOnce())
            ->method('getField')
            ->willReturn('customJoinField');
        $searchCriteriaMock->expects($this->exactly(2))
            ->method('getSortOrders')
            ->willReturn([$sortOrder]);
        /** @var AbstractDb|\PHPUnit\Framework\MockObject\MockObject $searchCriteriarMock */
        $collectionMock = $this->getMockBuilder(AbstractDb::class)
            ->disableOriginalConstructor()
            ->getMock();

        $model->process($searchCriteriaMock, $collectionMock);
    }
}
