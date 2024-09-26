<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\Model\DataProvider\Base;

use Magento\Elasticsearch\Model\DataProvider\Base\GetSuggestionFrequency;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Search\Api\SearchInterface;
use Magento\Framework\Api\Search\SearchCriteria;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Api\Filter;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GetSuggestionFrequencyTest extends TestCase
{
    /**
     * @var GetSuggestionFrequency
     */
    private $model;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SearchInterface
     */
    private $search;

    /**
     * @var Filter
     */
    private $filterMock;

    /**
     * @var SearchCriteria
     */
    private $searchCriteria;

    /**
     * @var SearchResultInterface
     */
    private $searchResult;

    /**
     * Set up test environment
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->filterBuilder = $this->getMockBuilder(FilterBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->searchCriteriaBuilder = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->search = $this->getMockBuilder(SearchInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->filterMock = $this->getMockBuilder(Filter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->searchCriteria = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->searchResult = $this->getMockBuilder(SearchResultInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getTotalCount'])
            ->getMockForAbstractClass();

        $objectManager = new ObjectManagerHelper($this);

        $this->model = $objectManager->getObject(
            GetSuggestionFrequency::class,
            [
                'filterBuilder' => $this->filterBuilder,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilder,
                'search' => $this->search
            ]
        );
    }

    /**
     * Test search suggestion frequency.
     * @return void
     */
    public function testGetItemsWithDisabledSearchSuggestion(): void
    {
        $this->filterBuilder->expects($this->once())
            ->method('setField')
            ->with('search_term')
            ->willReturnSelf();
        $this->filterBuilder->expects($this->once())
            ->method('setValue')
            ->with('mint')
            ->willReturnSelf();
        $this->filterBuilder->expects($this->once())
            ->method('create')
            ->willReturn($this->filterMock);
        $this->searchCriteriaBuilder->expects($this->once())
            ->method('addFilter')
            ->with($this->filterMock)
            ->willReturnSelf();
        $this->searchCriteriaBuilder->expects($this->once())
            ->method('create')
            ->willReturn($this->searchCriteria);
        $this->search->expects($this->once())
            ->method('search')
            ->with($this->searchCriteria)
            ->willReturn($this->searchResult);
        $this->searchResult->expects($this->once())
            ->method('getTotalCount')
            ->willReturn(5);
        $this->model->execute('mint');
    }
}
