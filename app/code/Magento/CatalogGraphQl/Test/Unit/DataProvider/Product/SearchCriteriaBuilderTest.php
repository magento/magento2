<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Test\Unit\DataProvider\Product;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\CatalogGraphQl\DataProvider\Product\SearchCriteriaBuilder;
use Magento\Eav\Model\Config;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteria\Builder;
use PHPUnit\Framework\TestCase;

/**
 * Build search criteria
 */
class SearchCriteriaBuilderTest extends TestCase
{
    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @var FilterBuilder
     */
    private FilterBuilder $filterBuilder;

    /**
     * @var FilterGroupBuilder
     */
    private FilterGroupBuilder $filterGroupBuilder;

    /**
     * @var Builder
     */
    private Builder $builder;

    /**
     * @var Visibility
     */
    private Visibility $visibility;

    /**
     * @var SortOrderBuilder
     */
    private SortOrderBuilder $sortOrderBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    private SearchCriteriaBuilder $model;

    /**
     * @var Config
     */
    private Config $eavConfig;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->builder = $this->createMock(Builder::class);
        $this->scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $this->filterBuilder = $this->createMock(FilterBuilder::class);
        $this->filterGroupBuilder = $this->createMock(FilterGroupBuilder::class);
        $this->sortOrderBuilder = $this->createMock(SortOrderBuilder::class);
        $this->visibility = $this->createMock(Visibility::class);
        $this->eavConfig = $this->createMock(Config::class);
        $this->model = new SearchCriteriaBuilder(
            $this->builder,
            $this->scopeConfig,
            $this->filterBuilder,
            $this->filterGroupBuilder,
            $this->visibility,
            $this->sortOrderBuilder,
            $this->eavConfig
        );
    }

    public function testBuild(): void
    {
        $args = ['search' => '', 'pageSize' => 20, 'currentPage' => 1];

        $filter = $this->createMock(Filter::class);

        $searchCriteria = $this->getMockBuilder(SearchCriteriaInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $attributeInterface = $this->getMockBuilder(Attribute::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $attributeInterface->setData(['is_filterable' => 0]);

        $this->builder->expects($this->any())
            ->method('build')
            ->with('products', $args)
            ->willReturn($searchCriteria);
        $searchCriteria->expects($this->any())->method('getFilterGroups')->willReturn([]);
        $this->eavConfig->expects($this->any())
            ->method('getAttribute')
            ->with(Product::ENTITY, 'price')
            ->willReturn($attributeInterface);
        $sortOrderList = ['relevance', '_id'];

        $this->sortOrderBuilder->expects($this->exactly(2))
            ->method('setField')
            ->willReturnCallback(fn($param) => match ([$param]) {
                [$sortOrderList[0]] => $this->sortOrderBuilder,
                [$sortOrderList[1]] => $this->sortOrderBuilder
            });

        $this->sortOrderBuilder->expects($this->exactly(2))
            ->method('setDirection')
            ->with('DESC')
            ->willReturnSelf();

        $this->sortOrderBuilder->expects($this->exactly(2))
            ->method('create')
            ->willReturn([]);

        $filterOrderList = ['search_term', 'visibility'];

        $this->filterBuilder->expects($this->exactly(2))
            ->method('setField')
            ->willReturnCallback(function ($filterOrderList) {
                if ([$filterOrderList[0]] || [$filterOrderList[1]]) {
                    return $this->filterBuilder;
                }
            });

        $this->filterBuilder->expects($this->exactly(2))
            ->method('setValue')
            ->with('')
            ->willReturnSelf();

        $this->filterBuilder->expects($this->exactly(2))
            ->method('setConditionType')
            ->willReturnCallback(function ($arg1) {
                if ($arg1 == 'in' || empty($arg1)) {
                    return $this->filterBuilder;
                }
            });

        $this->filterBuilder
            ->expects($this->exactly(2))
            ->method('create')
            ->willReturn($filter);

        $this->filterGroupBuilder->expects($this->any())
            ->method('addFilter')
            ->with($filter)
            ->willReturnSelf();

        $this->model->build($args, true);
    }
}
