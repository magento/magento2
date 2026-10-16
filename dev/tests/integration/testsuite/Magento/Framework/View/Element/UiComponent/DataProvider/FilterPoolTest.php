<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Element\UiComponent\DataProvider;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\Search\SearchCriteriaFactory;
use Magento\Framework\Data\Collection;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DataObject;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Database OR-group behavior is covered by {@see ReportingTest}.
 */
class FilterPoolTest extends TestCase
{
    /**
     * @var FilterPool
     */
    private FilterPool $filterPool;

    /**
     * @var FilterBuilder
     */
    private FilterBuilder $filterBuilder;

    /**
     * @var FilterGroupBuilder
     */
    private FilterGroupBuilder $filterGroupBuilder;

    /**
     * @var SearchCriteriaFactory
     */
    private SearchCriteriaFactory $searchCriteriaFactory;

    /**
     * @var EntityFactoryInterface
     */
    private EntityFactoryInterface $entityFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->filterPool = $objectManager->get(FilterPool::class);
        $this->filterBuilder = $objectManager->get(FilterBuilder::class);
        $this->filterGroupBuilder = $objectManager->get(FilterGroupBuilder::class);
        $this->searchCriteriaFactory = $objectManager->get(SearchCriteriaFactory::class);
        $this->entityFactory = $objectManager->get(EntityFactoryInterface::class);
    }

    /**
     * Loading a grid with a non-database collection and no filters must not fatal.
     */
    public function testApplyFiltersOnNonDbCollectionWithNoFiltersDoesNotThrow(): void
    {
        $collection = $this->createNonDbCollection();
        $criteria = $this->searchCriteriaFactory->create();
        $criteria->setFilterGroups([]);

        $this->filterPool->applyFilters($collection, $criteria);

        $this->assertCount(2, $collection->getItems());
    }

    /**
     * Filters on non-database collections are applied via addFieldToFilter (RegularFilter).
     */
    public function testApplyFiltersOnNonDbCollectionAppliesFieldFilters(): void
    {
        $collection = $this->createNonDbCollection();
        $this->assertCount(2, $collection->getItems());

        $filter = $this->filterBuilder
            ->setField('sku')
            ->setValue('SKU-1')
            ->setConditionType('eq')
            ->create();
        $filterGroup = $this->filterGroupBuilder->setFilters([$filter])->create();
        $criteria = $this->searchCriteriaFactory->create();
        $criteria->setFilterGroups([$filterGroup]);

        $this->filterPool->applyFilters($collection, $criteria);

        $items = $collection->getItems();
        $this->assertCount(1, $items);
        $item = current($items);
        $this->assertSame('SKU-1', $item->getData('sku'));
    }

    /**
     * Multiple filters on a non-database collection are all applied (AND via sequential filtering).
     */
    public function testApplyFiltersOnNonDbCollectionAppliesMultipleFilters(): void
    {
        $collection = $this->createNonDbCollection([
            ['sku' => 'A', 'status' => 1],
            ['sku' => 'B', 'status' => 1],
            ['sku' => 'A', 'status' => 0],
        ]);

        $skuFilter = $this->filterBuilder
            ->setField('sku')
            ->setValue('A')
            ->setConditionType('eq')
            ->create();
        $statusFilter = $this->filterBuilder
            ->setField('status')
            ->setValue(1)
            ->setConditionType('eq')
            ->create();

        $filterGroups = [
            $this->filterGroupBuilder->setFilters([$skuFilter])->create(),
            $this->filterGroupBuilder->setFilters([$statusFilter])->create(),
        ];
        $criteria = $this->searchCriteriaFactory->create();
        $criteria->setFilterGroups($filterGroups);

        $this->filterPool->applyFilters($collection, $criteria);

        $items = $collection->getItems();
        $this->assertCount(1, $items);
        $item = current($items);
        $this->assertSame('A', $item->getData('sku'));
        $this->assertSame(1, $item->getData('status'));
    }

    /**
     * Build an in-memory collection that supports addFieldToFilter (eq only).
     *
     * @param array $rows
     * @return Collection
     */
    private function createNonDbCollection(array $rows = []): Collection
    {
        if ($rows === []) {
            $rows = [
                ['sku' => 'SKU-1', 'name' => 'First'],
                ['sku' => 'SKU-2', 'name' => 'Second'],
            ];
        }

        $collection = new class ($this->entityFactory) extends Collection {
            /**
             * @var array
             */
            private $appliedFilters = [];

            /**
             * @inheritdoc
             */
            public function addFieldToFilter($field, $condition = null)
            {
                $this->appliedFilters[] = [$field, $condition];
                return $this;
            }

            /**
             * @inheritdoc
             */
            public function loadData($printQuery = false, $logQuery = false)
            {
                return $this;
            }

            /**
             * Filter loaded items in memory using recorded eq filters.
             *
             * @inheritdoc
             */
            public function getItems()
            {
                $items = parent::getItems();
                foreach ($this->appliedFilters as [$field, $condition]) {
                    if (!is_array($condition)) {
                        continue;
                    }
                    foreach ($condition as $type => $value) {
                        if ($type !== 'eq') {
                            continue;
                        }
                        $items = array_filter(
                            $items,
                            static function (DataObject $item) use ($field, $value) {
                                return (string) $item->getData($field) === (string) $value;
                            }
                        );
                    }
                }
                return $items;
            }
        };

        foreach ($rows as $row) {
            $collection->addItem(new DataObject($row));
        }

        return $collection;
    }
}
