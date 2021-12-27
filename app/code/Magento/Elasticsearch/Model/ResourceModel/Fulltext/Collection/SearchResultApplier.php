<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Elasticsearch\Model\ResourceModel\Fulltext\Collection;

use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\SearchResultApplierInterface;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Data\Collection;

/**
 * Resolve specific attributes for search criteria.
 */
class SearchResultApplier implements SearchResultApplierInterface
{
    /**
     * @var Collection|\Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection
     */
    private $collection;

    /**
     * @var SearchResultInterface
     */
    private $searchResult;

    /**
     * @var int
     */
    private $size;

    /**
     * @var int
     */
    private $currentPage;

    /**
     * @param Collection $collection
     * @param SearchResultInterface $searchResult
     * @param int $size
     * @param int $currentPage
     */
    public function __construct(
        Collection $collection,
        SearchResultInterface $searchResult,
        int $size,
        int $currentPage
    ) {
        $this->collection = $collection;
        $this->searchResult = $searchResult;
        $this->size = $size;
        $this->currentPage = $currentPage;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        if (empty($this->searchResult->getItems())) {
            $this->collection->getSelect()->where('NULL');

            return;
        }

        $ids = $this->getProductIdsBySalability();

        if (count($ids) == 0) {
            $items = $this->sliceItems($this->searchResult->getItems(), $this->size, $this->currentPage);
            foreach ($items as $item) {
                $ids[] = (int)$item->getId();
            }
        }
        $orderList = implode(',', $ids);
        $this->collection->getSelect()
            ->where('e.entity_id IN (?)', $ids)
            ->reset(\Magento\Framework\DB\Select::ORDER)
            ->order(new \Zend_Db_Expr("FIELD(e.entity_id,$orderList)"));
    }

    /**
     * Slice current items
     *
     * @param array $items
     * @param int $size
     * @param int $currentPage
     * @return array
     */
    private function sliceItems(array $items, int $size, int $currentPage): array
    {
        if ($size !== 0) {
            // Check that current page is in a range of allowed page numbers, based on items count and items per page,
            // than calculate offset for slicing items array.
            $itemsCount = count($items);
            $maxAllowedPageNumber = ceil($itemsCount/$size);
            if ($currentPage < 1) {
                $currentPage = 1;
            }
            if ($currentPage > $maxAllowedPageNumber) {
                $currentPage = $maxAllowedPageNumber;
            }

            $offset = $this->getOffset($currentPage, $size);
            $items = array_slice($items, $offset, $size);
        }

        return $items;
    }

    /**
     * Get offset for given page.
     *
     * @param int $pageNumber
     * @param int $pageSize
     * @return int
     */
    private function getOffset(int $pageNumber, int $pageSize): int
    {
        return ($pageNumber - 1) * $pageSize;
    }

    /**
     * Fetch filtered product ids sorted by the salability and other applied sort orders
     *
     * @return array
     */
    private function getProductIdsBySalability(): array
    {
        $ids = [];

        if ($this->collection->getFlag('has_stock_status_filter')) {
            $categoryId = null;
            $searchCriteria = $this->searchResult->getSearchCriteria();
            foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
                foreach ($filterGroup->getFilters() as $filter) {
                    if ($filter->getField() === 'category_ids') {
                        $categoryId = $filter->getValue();
                        break 2;
                    }
                }
            }

            if ($categoryId) {
                $searchOrders = $searchCriteria->getSortOrders();
                $searchOrders = array_merge(['is_salable' => \Magento\Framework\DB\Select::SQL_DESC], $searchOrders);
                $storeId = $this->collection->getStoreId();

                $connection = $this->collection->getConnection();
                $select = $connection->select()
                    ->from(
                        ['product' => $this->collection->getTable('catalog_product_entity')],
                        [
                            'product.entity_id',
                            'cat_index.position AS cat_index_position',
                            'stock_status_index.stock_status AS is_salable'
                        ]
                    )->join(
                        ['stock_status_index' => $this->collection->getTable('cataloginventory_stock_status')],
                        'product.entity_id = stock_status_index.product_id',
                        []
                    )->join(
                        ['cat_index' => $this->collection->getTable('catalog_category_product_index_store' . $storeId)],
                        'cat_index.product_id = product.entity_id'
                        . ' AND cat_index.category_id = ' . $categoryId
                        . ' AND cat_index.store_id = ' . $storeId,
                        []
                    );

                foreach ($searchOrders as $field => $dir) {
                    $select->order(new \Zend_Db_Expr("$field $dir"));
                }

                $offset = ($searchCriteria->getCurrentPage() * $searchCriteria->getPageSize());
                $select->limitPage($offset, $searchCriteria->getPageSize());
                $resultSet = $this->collection->getConnection()->fetchAssoc($select);

                foreach ($resultSet as $item) {
                    $ids[] = (int)$item['entity_id'];
                }
            }
        }

        return $ids;
    }
}
