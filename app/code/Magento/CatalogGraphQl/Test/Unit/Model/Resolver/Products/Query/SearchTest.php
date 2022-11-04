<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Test\Unit\Model\Resolver\Products\Query;

use Magento\CatalogGraphQl\DataProvider\Product\SearchCriteriaBuilder;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\ProductSearch;
use Magento\CatalogGraphQl\Model\Resolver\Products\Query\FieldSelection;
use Magento\CatalogGraphQl\Model\Resolver\Products\Query\Search;
use Magento\CatalogGraphQl\Model\Resolver\Products\Query\Search\QueryPopularity;
use Magento\CatalogGraphQl\Model\Resolver\Products\SearchResultFactory;
use Magento\CatalogGraphQl\Model\Resolver\Products\Query\Suggestions;
use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\GraphQl\Query\Resolver\ArgumentsProcessorInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextExtensionInterface;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Search\Api\SearchInterface;
use Magento\Search\Model\Search\PageSizeProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for fulltext search query
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SearchTest extends TestCase
{
    /**
     * @var SearchInterface|MockObject
     */
    private $search;

    /**
     * @var SearchResultFactory|MockObject
     */
    private $searchResultFactory;

    /**
     * @var PageSizeProvider|MockObject
     */
    private $pageSizeProvider;

    /**
     * @var FieldSelection|MockObject
     */
    private $fieldSelection;

    /**
     * @var ArgumentsProcessorInterface|MockObject
     */
    private $argsSelection;

    /**
     * @var ProductSearch|MockObject
     */
    private $productsProvider;

    /**
     * @var SearchCriteriaBuilder|MockObject
     */
    private $searchCriteriaBuilder;

    /**
     * @var Suggestions|MockObject
     */
    private $suggestions;

    /**
     * @var QueryPopularity|MockObject
     */
    private $queryPopularity;

    /**
     * @var Search
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->search = $this->getMockBuilder(SearchInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->searchResultFactory = $this->getMockBuilder(SearchResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageSizeProvider = $this->getMockBuilder(PageSizeProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->fieldSelection = $this->getMockBuilder(FieldSelection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->argsSelection = $this->getMockBuilder(ArgumentsProcessorInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->productsProvider = $this->getMockBuilder(ProductSearch::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilder = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->suggestions = $this->getMockBuilder(Suggestions::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->queryPopularity = $this->getMockBuilder(QueryPopularity::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = new Search(
            $this->search,
            $this->searchResultFactory,
            $this->pageSizeProvider,
            $this->fieldSelection,
            $this->productsProvider,
            $this->searchCriteriaBuilder,
            $this->argsSelection,
            $this->suggestions,
            $this->queryPopularity
        );
    }

    public function testPopulateSearchQueryStats(): void
    {
        $args = ['search' => 'test'];
        $context = $this->getMockBuilder(ContextInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $resolveInfo = $this->getMockBuilder(ResolveInfo::class)
            ->disableOriginalConstructor()
            ->getMock();

        $searchCriteria = $this->getMockBuilder(SearchCriteriaInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->searchCriteriaBuilder->expects($this->any())
            ->method('build')
            ->willReturn($searchCriteria);
        $results = $this->getMockBuilder(SearchResultInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->search->expects($this->once())
            ->method('search')
            ->with($searchCriteria)
            ->willReturn($results);
        $this->productsProvider->expects($this->any())->method('getList')->willReturn($results);
        $results->expects($this->any())->method('getItems')->willReturn([]);

        $this->queryPopularity->expects($this->once())
            ->method('execute')
            ->with($context, $args['search'], 0);

        $this->model->getResult($args, $resolveInfo, $context);
    }
}
