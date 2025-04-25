<?php
/**
 * Copyright 2024 Adobe
 * All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Test\Unit\DataProvider\Product;

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\CatalogGraphQl\DataProvider\Product\RequestDataBuilder;
use Magento\CatalogGraphQl\DataProvider\Product\SearchCriteriaBuilder;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\SearchCriteriaResolverFactory;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\SearchCriteriaResolverInterface;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\Search\SearchCriteria;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteria\ArgumentApplierPool;
use Magento\Framework\Search\Request\Config as SearchConfig;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Build search criteria
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SearchCriteriaBuilderTest extends TestCase
{
    /**
     * @var ScopeConfigInterface|MockObject
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @var FilterBuilder|MockObject
     */
    private FilterBuilder $filterBuilder;

    /**
     * @var FilterGroupBuilder|MockObject
     */
    private FilterGroupBuilder $filterGroupBuilder;

    /**
     * @var Visibility|MockObject
     */
    private Visibility $visibility;

    /**
     * @var SortOrderBuilder|MockObject
     */
    private SortOrderBuilder $sortOrderBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    private SearchCriteriaBuilder $model;

    /**
     * @var ProductAttributeRepositoryInterface|MockObject
     */
    private ProductAttributeRepositoryInterface $productAttributeRepository;

    /**
     * @var SearchConfig|MockObject
     */
    private SearchConfig $searchConfig;

    /**
     * @var RequestDataBuilder|MockObject
     */
    private RequestDataBuilder $localData;

    /**
     * @var SearchCriteriaResolverFactory|MockObject
     */
    private SearchCriteriaResolverFactory $criteriaResolverFactory;

    /**
     * @var ArgumentApplierPool|MockObject
     */
    private ArgumentApplierPool $argumentApplierPool;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $this->filterBuilder = $this->createMock(FilterBuilder::class);
        $this->filterGroupBuilder = $this->createMock(FilterGroupBuilder::class);
        $this->sortOrderBuilder = $this->createMock(SortOrderBuilder::class);
        $this->visibility = $this->createMock(Visibility::class);
        $this->productAttributeRepository = $this->createMock(ProductAttributeRepositoryInterface::class);
        $this->searchConfig = $this->createMock(SearchConfig::class);
        $this->localData = $this->createMock(RequestDataBuilder::class);
        $this->criteriaResolverFactory = $this->createMock(SearchCriteriaResolverFactory::class);
        $this->argumentApplierPool = $this->createMock(ArgumentApplierPool::class);
        $this->model = new SearchCriteriaBuilder(
            $this->scopeConfig,
            $this->filterBuilder,
            $this->filterGroupBuilder,
            $this->visibility,
            $this->sortOrderBuilder,
            $this->productAttributeRepository,
            $this->searchConfig,
            $this->localData,
            $this->criteriaResolverFactory,
            $this->argumentApplierPool,
        );
    }

    public function testBuild(): void
    {
        $args = ['search' => '', 'pageSize' => 20, 'currentPage' => 1];

        $filter = $this->createMock(Filter::class);
        $searchCriteria = $this->createMock(SearchCriteria::class);
        $attributeInterface = $this->getMockBuilder(Attribute::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $attributeInterface->setData(['is_filterable' => 0]);

        $searchCriteriaResolver = $this->createMock(SearchCriteriaResolverInterface::class);
        $this->criteriaResolverFactory->expects(self::once())
            ->method('create')
            ->willReturn($searchCriteriaResolver);
        $searchCriteriaResolver->expects(self::once())
            ->method('resolve')
            ->willReturn($searchCriteria);
        $searchCriteria->expects($this->any())->method('getFilterGroups')->willReturn([]);
        $this->productAttributeRepository->expects(self::once())
            ->method('get')
            ->with('price')
            ->willReturn($attributeInterface);
        $sortOrderList = ['relevance', 'entity_id'];

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
