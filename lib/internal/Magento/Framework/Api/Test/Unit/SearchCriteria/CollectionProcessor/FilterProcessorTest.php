<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Api\Test\Unit\SearchCriteria\CollectionProcessor;

use Magento\Framework\Api\Filter;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor\FilterProcessor;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor\FilterProcessor\CustomFilterInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FilterProcessorTest extends TestCase
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
        /** @var CustomFilterInterface|MockObject $customFilterMock */
        $customFilterMock = $this->getMockBuilder(CustomFilterInterface::class)
            ->getMock();

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

        $resultFieldsOne = [
            $otherFilterFieldMapped,
        ];
        $resultConditionsOne = [
            [
                $otherFilterFieldCondition => $otherFilterFieldValue,
            ],
        ];
        $resultFieldsTwo = [
            $thirdField,
        ];
        $resultConditionsTwo = [
            [
                'eq' => $thirdFieldValue,
            ],
        ];

        $model = $this->getModel($customFilters, $fieldMapping);

        /** @var FilterGroup|MockObject $filterGroupOneMock */
        $filterGroupOneMock = $this->getMockBuilder(FilterGroup::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var FilterGroup|MockObject $filterGroupTwoMock */
        $filterGroupTwoMock = $this->getMockBuilder(FilterGroup::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var Filter|MockObject $filterOneMock */
        $filterOneMock = $this->getMockBuilder(Filter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $filterOneMock->expects($this->once())
            ->method('getField')
            ->willReturn($customFilterField);

        /** @var Filter|MockObject $filterTwoMock */
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

        /** @var Filter|MockObject $filterThreeMock */
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

        /** @var SearchCriteriaInterface|MockObject $searchCriteriaMock */
        $searchCriteriaMock = $this->getMockBuilder(SearchCriteriaInterface::class)
            ->getMock();

        $searchCriteriaMock->expects($this->once())
            ->method('getFilterGroups')
            ->willReturn([$filterGroupOneMock, $filterGroupTwoMock]);

        /** @var AbstractDb|MockObject $searchCriteriarMock */
        $collectionMock = $this->getMockBuilder(AbstractDb::class)
            ->disableOriginalConstructor()
            ->getMock();

        $customFilterMock->expects($this->once())
            ->method('apply')
            ->with($filterOneMock, $collectionMock)
            ->willReturn(true);

        $collectionMock->expects($this->exactly(2))
            ->method('addFieldToFilter')
            ->willReturnCallback(function (
                $arg1,
                $arg2
            ) use (
                $collectionMock,
                $resultFieldsOne,
                $resultConditionsOne,
                $resultFieldsTwo,
                $resultConditionsTwo
            ) {
                if ($arg1 == $resultFieldsOne && $arg2 == $resultConditionsOne) {
                    return $collectionMock;
                } elseif ($arg1 == $resultFieldsTwo && $arg2 == $resultConditionsTwo) {
                    return $collectionMock;
                }
            });

        $model->process($searchCriteriaMock, $collectionMock);
    }

    public function testProcessWithException()
    {
        $this->expectException('InvalidArgumentException');
        /** @var \stdClass|MockObject $customFilterMock */
        $customFilterMock = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['apply'])
            ->getMock();

        $customFilterField = 'customFilterField';
        $customFilters = [$customFilterField => $customFilterMock];

        $model = $this->getModel($customFilters, []);

        /** @var FilterGroup|MockObject $filterGroupOneMock */
        $filterGroupOneMock = $this->getMockBuilder(FilterGroup::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var Filter|MockObject $filterOneMock */
        $filterOneMock = $this->getMockBuilder(Filter::class)
            ->disableOriginalConstructor()
            ->getMock();
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
