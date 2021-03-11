<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Test\Unit\Model\Api\SearchCriteria\CollectionProcessor;

use Magento\Eav\Model\Api\SearchCriteria\CollectionProcessor\FilterProcessor;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor\FilterProcessor\CustomFilterInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Data\Collection\AbstractDb;

class FilterProcessorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Return model
     *
     * @param CustomFilterInterface[] $customFilters
     * @param array $fieldMapping
     * @return FilterProcessor
     */
    private function getModel(array $customFilters, array $fieldMapping)
    {
        return new FilterProcessor($customFilters, $fieldMapping);
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testProcess()
    {
        /** @var CustomFilterInterface|\PHPUnit\Framework\MockObject\MockObject $customFilterMock */
        $customFilterMock = $this->createPartialMock(CustomFilterInterface::class, ['apply']);

        $customFilterField = 'customFilterField';
        $customFilters = [$customFilterField => $customFilterMock];

        $otherFilterField = 'otherFilterField';
        $otherFilterFieldMapped = 'otherFilterFieldMapped';
        $fieldMapping = [$otherFilterField => $otherFilterFieldMapped];
        $otherFilterFieldValue = 'otherFilterFieldValue';
        $otherFilterFieldCondition = 'gt';

        $thirdField = 'thirdField';
        $thirdFieldValue = 'thirdFieldValue';
        $thirdFieldCondition = '';

        $resultOne = [
            [
                'attribute' => $otherFilterFieldMapped,
                $otherFilterFieldCondition => $otherFilterFieldValue,
            ],
        ];
        $resultTwo = [
            [
                'attribute' => $thirdField,
                'eq' => $thirdFieldValue,
            ],
        ];

        $model = $this->getModel($customFilters, $fieldMapping);

        /** @var FilterGroup|\PHPUnit\Framework\MockObject\MockObject $filterGroupOneMock */
        $filterGroupOneMock = $this->getMockBuilder(FilterGroup::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var FilterGroup|\PHPUnit\Framework\MockObject\MockObject $filterGroupTwoMock */
        $filterGroupTwoMock = $this->getMockBuilder(FilterGroup::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var Filter|\PHPUnit\Framework\MockObject\MockObject $filterOneMock */
        $filterOneMock = $this->getMockBuilder(Filter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $filterOneMock->expects($this->once())
            ->method('getField')
            ->willReturn($customFilterField);

        /** @var Filter|\PHPUnit\Framework\MockObject\MockObject $filterTwoMock */
        $filterTwoMock = $this->getMockBuilder(Filter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $filterTwoMock->expects($this->exactly(2))
            ->method('getField')
            ->willReturn($otherFilterField);
        $filterTwoMock->expects($this->once())
            ->method('getValue')
            ->willReturn($otherFilterFieldValue);
        $filterTwoMock->expects($this->exactly(2))
            ->method('getConditionType')
            ->willReturn($otherFilterFieldCondition);

        /** @var Filter|\PHPUnit\Framework\MockObject\MockObject $filterThreeMock */
        $filterThreeMock = $this->getMockBuilder(Filter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $filterThreeMock->expects($this->exactly(2))
            ->method('getField')
            ->willReturn($thirdField);
        $filterThreeMock->expects($this->once())
            ->method('getValue')
            ->willReturn($thirdFieldValue);
        $filterThreeMock->expects($this->once())
            ->method('getConditionType')
            ->willReturn($thirdFieldCondition);

        $filterGroupOneMock->expects($this->once())
            ->method('getFilters')
            ->willReturn([$filterOneMock, $filterTwoMock]);

        $filterGroupTwoMock->expects($this->once())
            ->method('getFilters')
            ->willReturn([$filterThreeMock]);

        /** @var SearchCriteriaInterface|\PHPUnit\Framework\MockObject\MockObject $searchCriteriaMock */
        $searchCriteriaMock = $this->getMockBuilder(SearchCriteriaInterface::class)
            ->getMock();

        $searchCriteriaMock->expects($this->once())
            ->method('getFilterGroups')
            ->willReturn([$filterGroupOneMock, $filterGroupTwoMock]);

        /** @var AbstractDb|\PHPUnit\Framework\MockObject\MockObject $searchCriteriarMock */
        $collectionMock = $this->getMockBuilder(AbstractDb::class)
            ->disableOriginalConstructor()
            ->getMock();

        $customFilterMock->expects($this->once())
            ->method('apply')
            ->with($filterOneMock, $collectionMock)
            ->willReturn(true);

        $collectionMock->expects($this->exactly(2))
            ->method('addFieldToFilter')
            ->withConsecutive(
                [$resultOne],
                [$resultTwo]
            )->willReturnSelf();

        $model->process($searchCriteriaMock, $collectionMock);
    }

    /**
     */
    public function testProcessWithException()
    {
        $this->expectException(\InvalidArgumentException::class);

        /** @var \stdClass|\PHPUnit\Framework\MockObject\MockObject $customFilterMock */
        $customFilterMock = $this->createPartialMock(\stdClass::class, ['apply']);

        $customFilterField = 'customFilterField';
        $customFilters = [$customFilterField => $customFilterMock];

        $model = $this->getModel($customFilters, []);

        /** @var FilterGroup|\PHPUnit\Framework\MockObject\MockObject $filterGroupOneMock */
        $filterGroupOneMock = $this->getMockBuilder(FilterGroup::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var Filter|\PHPUnit\Framework\MockObject\MockObject $filterOneMock */
        $filterOneMock = $this->getMockBuilder(Filter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $filterOneMock->expects($this->once())
            ->method('getField')
            ->willReturn($customFilterField);

        $filterGroupOneMock->expects($this->once())
            ->method('getFilters')
            ->willReturn([$filterOneMock]);

        /** @var SearchCriteriaInterface|\PHPUnit\Framework\MockObject\MockObject $searchCriteriaMock */
        $searchCriteriaMock = $this->getMockBuilder(SearchCriteriaInterface::class)
            ->getMock();

        $searchCriteriaMock->expects($this->once())
            ->method('getFilterGroups')
            ->willReturn([$filterGroupOneMock]);

        /** @var AbstractDb|\PHPUnit\Framework\MockObject\MockObject $searchCriteriarMock */
        $collectionMock = $this->getMockBuilder(AbstractDb::class)
            ->disableOriginalConstructor()
            ->getMock();

        $customFilterMock->expects($this->never())
            ->method('apply');

        $collectionMock->expects($this->never())
            ->method('addFieldToFilter');

        $model->process($searchCriteriaMock, $collectionMock);
    }
}
