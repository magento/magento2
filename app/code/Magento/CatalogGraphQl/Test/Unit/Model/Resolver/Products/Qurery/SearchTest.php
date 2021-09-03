<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Test\Unit\Model\Resolver\Products\Query;

use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\GraphQl\Query\Resolver\ArgumentsProcessorInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\GraphQl\Model\Query\ContextExtensionInterface;
use Magento\CatalogGraphQl\Model\Resolver\Products\SearchResultFactory;
use Magento\CatalogGraphQl\Model\Resolver\Products\Query\FieldSelection;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\ProductSearch;
use Magento\CatalogGraphQl\DataProvider\Product\SearchCriteriaBuilder;
use Magento\CatalogGraphQl\Model\Resolver\Products\Query\Search;
use Magento\Search\Model\QueryFactory;
use Magento\Search\Model\Query;
use Magento\Search\Model\Search\PageSizeProvider;
use Magento\Search\Api\SearchInterface;
use Magento\Store\Api\Data\StoreInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;


/**
 * Test for fulltext search query
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
     * @var QueryFactory|MockObject
     */
    private $queryFactory;

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
        $this->queryFactory = $this->getMockBuilder(QueryFactory::class)
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
            $this->queryFactory
        );
    }

    public function testPopulateSearchQueryStats(): void
    {
        $args = ['search' => 'test'];
        $storeId = 1;

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
        $query = $this->getMockBuilder(Query::class)
            ->disableOriginalConstructor()
            ->getMock();
        $query->expects($this->once())->method('setStoreId')->with($storeId);
        $query->expects($this->once())->method('saveIncrementalPopularity');
        $query->expects($this->once())->method('saveNumResults');
        $this->queryFactory->expects($this->once())
            ->method('get')
            ->willReturn($query);
        $extensionAttributes = $this->getMockBuilder(ContextExtensionInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $context->expects($this->any())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttributes);
        $store = $this->getMockBuilder(StoreInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $store->expects($this->any())->method('getId')->willReturn($storeId);
        $extensionAttributes->expects($this->any())->method('getStore')->willReturn($store);
        $this->productsProvider->expects($this->any())->method('getList')->willReturn($results);
        $results->expects($this->any())->method('getItems')->willReturn([]);

        $this->model->getResult($args, $resolveInfo, $context);
    }
}
