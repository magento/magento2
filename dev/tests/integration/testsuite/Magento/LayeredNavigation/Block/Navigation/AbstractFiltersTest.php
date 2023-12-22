<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LayeredNavigation\Block\Navigation;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Catalog\Model\Layer\Filter\Item;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\CatalogSearch\Model\Indexer\Fulltext\Processor;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Search\Request\Builder;
use Magento\Framework\Search\Request\Config;
use Magento\Framework\View\LayoutInterface;
use Magento\LayeredNavigation\Block\Navigation;
use Magento\LayeredNavigation\Block\Navigation\Category as CategoryNavigationBlock;
use Magento\LayeredNavigation\Block\Navigation\Search as SearchNavigationBlock;
use Magento\Search\Model\Search;
use Magento\Store\Model\Store;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Base class for custom filters in navigation block on category page.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class AbstractFiltersTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var CollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * @var Navigation
     */
    protected $navigationBlock;

    /**
     * @var LayoutInterface
     */
    protected $layout;

    /**
     * @var ProductAttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManager = Bootstrap::getObjectManager();
        $this->categoryCollectionFactory = $this->objectManager->create(CollectionFactory::class);
        $this->layout = $this->objectManager->get(LayoutInterface::class);
        $this->attributeRepository = $this->objectManager->create(ProductAttributeRepositoryInterface::class);
        $this->productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        $this->createNavigationBlockInstance();
    }

    /**
     * Returns layer type for navigation block.
     *
     * @return string
     */
    abstract protected function getLayerType(): string;

    /**
     * Returns attribute code.
     *
     * @return string
     */
    protected function getAttributeCode(): string
    {
        return '';
    }

    /**
     * Tests getFilters method from navigation block on category page.
     *
     * @param array $products
     * @param array $attributeData
     * @param array $expectation
     * @param string $categoryName
     * @return void
     */
    protected function getCategoryFiltersAndAssert(
        array $products,
        array $attributeData,
        array $expectation,
        string $categoryName
    ): void {
        $this->updateAttribute($attributeData, $this->getAttributeCode());
        $this->updateProducts($products, $this->getAttributeCode());
        $this->clearInstanceAndReindexSearch();
        $category = $this->loadCategory($categoryName, Store::DEFAULT_STORE_ID);
        $this->navigationBlock->getLayer()->setCurrentCategory($category);
        $this->navigationBlock->setLayout($this->layout);
        $filter = $this->getFilterByCode($this->navigationBlock->getFilters(), $this->getAttributeCode());

        if ($attributeData['is_filterable']) {
            $this->assertNotNull($filter);
            $preparedItems = $this->prepareFilterItems($filter);
            $this->assertCount(count($expectation), $preparedItems);

            foreach ($preparedItems as $key => $preparedItem) {
                $this->assertEquals($expectation[$key], $preparedItem);
                $item = $filter->getItems()[$key];
                $this->createNavigationBlockInstance();
                $this->navigationBlock->getRequest()->setParams(
                    $this->getRequestParams((string) $item->getValueString())
                );
                $this->navigationBlock->getLayer()->setCurrentCategory($category);
                $this->navigationBlock->setLayout($this->layout);
                $collectionSize = $this->navigationBlock->getLayer()->getProductCollection()->count();
                $this->assertEquals($expectation[$key]['count'], $collectionSize);
                $this->assertEquals($collectionSize, $item->getData('count'));
            }
        } else {
            $this->assertNull($filter);
        }
    }

    /**
     * Tests getFilters method from navigation block layer state on category page.
     *
     * @param array $products
     * @param array $expectation
     * @param string $categoryName
     * @param string|null $filterValue
     * @param int $productsCount
     * @return void
     */
    protected function getCategoryActiveFiltersAndAssert(
        array $products,
        array $expectation,
        string $categoryName,
        string $filterValue,
        int $productsCount
    ): void {
        $this->updateAttribute(
            ['is_filterable' => AbstractFilter::ATTRIBUTE_OPTIONS_ONLY_WITH_RESULTS],
            $this->getAttributeCode()
        );
        $this->updateProducts($products, $this->getAttributeCode());
        $this->clearInstanceAndReindexSearch();
        $this->navigationBlock->getRequest()->setParams($this->getRequestParams($filterValue));
        $this->navigationBlock->getLayer()->setCurrentCategory(
            $this->loadCategory($categoryName, Store::DEFAULT_STORE_ID)
        );
        $this->navigationBlock->setLayout($this->layout);
        $activeFilters = $this->navigationBlock->getLayer()->getState()->getFilters();
        $this->assertCount(1, $activeFilters);
        $currentFilter = reset($activeFilters);
        $this->assertActiveFilter($expectation, $currentFilter);
        $this->assertEquals($productsCount, $this->navigationBlock->getLayer()->getProductCollection()->getSize());
    }

    /**
     * Tests getFilters method from navigation block on search page.
     *
     * @param array $products
     * @param array $attributeData
     * @param array $expectation
     * @return void
     */
    protected function getSearchFiltersAndAssert(
        array $products,
        array $attributeData,
        array $expectation
    ): void {
        $this->updateAttribute($attributeData, $this->getAttributeCode());
        $this->updateProducts($products, $this->getAttributeCode());
        $this->clearInstanceAndReindexSearch();
        $this->navigationBlock->getRequest()->setParams(['q' => $this->getSearchString()]);
        $this->navigationBlock->setLayout($this->layout);
        $filter = $this->getFilterByCode($this->navigationBlock->getFilters(), $this->getAttributeCode());

        if ($attributeData['is_filterable_in_search']) {
            $this->assertNotNull($filter);
            $this->assertEquals($expectation, $this->prepareFilterItems($filter));
        } else {
            $this->assertNull($filter);
        }
    }

    /**
     * Tests getFilters method from navigation block layer state on search page.
     *
     * @param array $products
     * @param array $expectation
     * @param string $filterValue
     * @param int $productsCount
     * @return void
     */
    protected function getSearchActiveFiltersAndAssert(
        array $products,
        array $expectation,
        string $filterValue,
        int $productsCount
    ): void {
        $this->updateAttribute(
            ['is_filterable' => AbstractFilter::ATTRIBUTE_OPTIONS_ONLY_WITH_RESULTS, 'is_filterable_in_search' => 1],
            $this->getAttributeCode()
        );
        $this->updateProducts($products, $this->getAttributeCode());
        $this->clearInstanceAndReindexSearch();
        $this->navigationBlock->getRequest()->setParams(
            array_merge($this->getRequestParams($filterValue), ['q' => $this->getSearchString()])
        );
        $this->navigationBlock->setLayout($this->layout);
        $activeFilters = $this->navigationBlock->getLayer()->getState()->getFilters();
        $this->assertCount(1, $activeFilters);
        $currentFilter = reset($activeFilters);
        $this->assertActiveFilter($expectation, $currentFilter);
        $this->assertEquals($productsCount, $this->navigationBlock->getLayer()->getProductCollection()->getSize());
    }

    /**
     * Returns filter with specified attribute.
     *
     * @param AbstractFilter[] $filters
     * @param string $code
     * @return AbstractFilter|null
     */
    protected function getFilterByCode(array $filters, string $code): ?AbstractFilter
    {
        $filter = array_filter(
            $filters,
            function (AbstractFilter $filter) use ($code) {
                return $filter->getData('attribute_model')
                    && $filter->getData('attribute_model')->getAttributeCode() === $code;
            }
        );

        return array_shift($filter);
    }

    /**
     * Updates attribute data.
     *
     * @param array $data
     * @param string $attributeCode
     * @return void
     */
    protected function updateAttribute(
        array $data,
        string $attributeCode
    ): void {
        $attribute = $this->attributeRepository->get($attributeCode);
        $attribute->setDataChanges(false);
        $attribute->addData($data);

        if ($attribute->hasDataChanges()) {
            $this->attributeRepository->save($attribute);
        }
    }

    /**
     * Returns filter items as array.
     *
     * @param AbstractFilter $filter
     * @return array
     */
    protected function prepareFilterItems(AbstractFilter $filter): array
    {
        $items = [];
        /** @var Item $item */
        foreach ($filter->getItems() as $item) {
            $items[] = [
                'label' => $item->getData('label'),
                'count' => $item->getData('count'),
            ];
        }

        return $items;
    }

    /**
     * Update products data by attribute.
     *
     * @param array $products
     * @param string $attributeCode
     * @param int $storeId
     * @return void
     */
    protected function updateProducts(
        array $products,
        string $attributeCode,
        int $storeId = Store::DEFAULT_STORE_ID
    ): void {
        $attribute = $this->attributeRepository->get($attributeCode);

        foreach ($products as $productSku => $stringValue) {
            $product = $this->productRepository->get($productSku, false, $storeId, true);
            $productValue = $attribute->usesSource()
                ? $attribute->getSource()->getOptionId($stringValue)
                : $stringValue;
            $product->addData(
                [$attribute->getAttributeCode() => $productValue]
            );
            $this->productRepository->save($product);
        }
    }

    /**
     * Clears instances and rebuilds seqrch index.
     *
     * @return void
     */
    protected function clearInstanceAndReindexSearch(): void
    {
        $this->objectManager->removeSharedInstance(Config::class);
        $this->objectManager->removeSharedInstance(Builder::class);
        $this->objectManager->removeSharedInstance(Search::class);
        $this->objectManager->create(Processor::class)->reindexAll();
    }

    /**
     * Loads category by id.
     *
     * @param string $categoryName
     * @param int $storeId
     * @return CategoryInterface
     */
    protected function loadCategory(string $categoryName, int $storeId): CategoryInterface
    {
        /** @var Collection $categoryCollection */
        $categoryCollection = $this->categoryCollectionFactory->create();
        /** @var CategoryInterface $category */
        $category = $categoryCollection->setStoreId($storeId)
            ->addAttributeToSelect('display_mode', 'left')
            ->addAttributeToFilter(CategoryInterface::KEY_NAME, $categoryName)
            ->setPageSize(1)
            ->getFirstItem();
        $category->setStoreId($storeId);

        return $category;
    }

    /**
     * Creates navigation block instance.
     *
     * @return void
     */
    protected function createNavigationBlockInstance(): void
    {
        if ($this->getLayerType() === Resolver::CATALOG_LAYER_SEARCH) {
            $class = SearchNavigationBlock::class;
            $this->objectManager->removeSharedInstance('searchFilterList');
        } else {
            $class = CategoryNavigationBlock::class;
            $this->objectManager->removeSharedInstance('categoryFilterList');
        }

        $layerResolver = $this->objectManager->create(Resolver::class);
        $layerResolver->create($this->getLayerType());
        $this->navigationBlock = $this->objectManager->create(
            $class,
            [
                'layerResolver' => $layerResolver,
            ]
        );
    }

    /**
     * Returns search query for filters on search page.
     *
     * @return string
     */
    protected function getSearchString(): string
    {
        return 'Simple Product';
    }

    /**
     * Adds params for filtering.
     *
     * @param string $filterValue
     * @return array
     */
    protected function getRequestParams(string $filterValue): array
    {
        $attribute = $this->attributeRepository->get($this->getAttributeCode());
        $filterValue = $attribute->usesSource()
            ? $attribute->getSource()->getOptionId($filterValue)
            : $filterValue;

        return [$this->getAttributeCode() => $filterValue];
    }

    /**
     * Asserts active filter data.
     *
     * @param array $expectation
     * @param Item $currentFilter
     * @return void
     */
    protected function assertActiveFilter(array $expectation, Item $currentFilter): void
    {
        $this->assertEquals($expectation['label'], $currentFilter->getData('label'));
        $this->assertEquals($expectation['count'], $currentFilter->getData('count'));
        $this->assertEquals(
            $this->getAttributeCode(),
            $currentFilter->getFilter()->getData('attribute_model')->getAttributeCode()
        );
    }
}
