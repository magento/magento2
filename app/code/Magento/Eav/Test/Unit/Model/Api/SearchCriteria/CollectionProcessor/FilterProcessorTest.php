<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Eav\Test\Unit\Model\Api\SearchCriteria\CollectionProcessor;

use Magento\Eav\Model\Api\SearchCriteria\CollectionProcessor\FilterProcessor;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor\FilterProcessor\CustomFilterInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

class FilterProcessorTest extends TestCase
{
    use MockCreationTrait;

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
        /** @var CustomFilterInterface|MockObject $customFilterMock */
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

        /** @var FilterGroup|MockObject $filterGroupOneMock */
        $filterGroupOneMock = $this->createMock(FilterGroup::class);

        /** @var FilterGroup|MockObject $filterGroupTwoMock */
        $filterGroupTwoMock = $this->createMock(FilterGroup::class);

        /** @var Filter|MockObject $filterOneMock */
        $filterOneMock = $this->createMock(Filter::class);
        $filterOneMock->expects($this->once())
            ->method('getField')
            ->willReturn($customFilterField);

        /** @var Filter|MockObject $filterTwoMock */
        $filterTwoMock = $this->createMock(Filter::class);
        $filterTwoMock->expects($this->exactly(2))
            ->method('getField')
            ->willReturn($otherFilterField);
        $filterTwoMock->expects($this->once())
            ->method('getValue')
            ->willReturn($otherFilterFieldValue);
        $filterTwoMock->expects($this->exactly(2))
            ->method('getConditionType')
            ->willReturn($otherFilterFieldCondition);

        /** @var Filter|MockObject $filterThreeMock */
        $filterThreeMock = $this->createMock(Filter::class);
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

        /** @var SearchCriteriaInterface|MockObject $searchCriteriaMock */
        $searchCriteriaMock = $this->getMockBuilder(SearchCriteriaInterface::class)
            ->getMock();

        $searchCriteriaMock->expects($this->once())
            ->method('getFilterGroups')
            ->willReturn([$filterGroupOneMock, $filterGroupTwoMock]);

        /** @var AbstractDb|MockObject $searchCriteriarMock */
        $collectionMock = $this->createMock(AbstractDb::class);

        $customFilterMock->expects($this->once())
            ->method('apply')
            ->with($filterOneMock, $collectionMock)
            ->willReturn(true);

        $collectionMock->expects($this->exactly(2))
            ->method('addFieldToFilter')
            ->willReturnCallback(function ($arg1) use ($resultOne, $resultTwo, $collectionMock) {
                if ($arg1 == $resultOne || $arg1 == $resultTwo) {
                    return $collectionMock;
                }
            });

        $model->process($searchCriteriaMock, $collectionMock);
    }

    public function testProcessWithException()
    {
        $this->expectException('InvalidArgumentException');
        /** @var \stdClass|MockObject $customFilterMock */
        $customFilterMock = $this->createPartialMockWithReflection(
            \stdClass::class,
            ['apply']
        );

        $customFilterField = 'customFilterField';
        $customFilters = [$customFilterField => $customFilterMock];

        $model = $this->getModel($customFilters, []);

        /** @var FilterGroup|MockObject $filterGroupOneMock */
        $filterGroupOneMock = $this->createMock(FilterGroup::class);

        /** @var Filter|MockObject $filterOneMock */
        $filterOneMock = $this->createMock(Filter::class);
        $filterOneMock->expects($this->once())
            ->method('getField')
            ->willReturn($customFilterField);

        $filterGroupOneMock->expects($this->once())
            ->method('getFilters')
            ->willReturn([$filterOneMock]);

        /** @var SearchCriteriaInterface|MockObject $searchCriteriaMock */
        $searchCriteriaMock = $this->getMockBuilder(SearchCriteriaInterface::class)
            ->getMock();

        $searchCriteriaMock->expects($this->once())
            ->method('getFilterGroups')
            ->willReturn([$filterGroupOneMock]);

        /** @var AbstractDb|MockObject $searchCriteriarMock */
        $collectionMock = $this->createMock(AbstractDb::class);

        $customFilterMock->expects($this->never())
            ->method('apply');

        $collectionMock->expects($this->never())
            ->method('addFieldToFilter');

        $model->process($searchCriteriaMock, $collectionMock);
    }
}
