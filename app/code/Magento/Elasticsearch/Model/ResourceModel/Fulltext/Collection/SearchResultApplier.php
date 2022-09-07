<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Model\ResourceModel\Fulltext\Collection;

use Magento\CatalogInventory\Model\ResourceModel\StockStatusFilterInterface;
use Magento\CatalogInventory\Model\StockStatusApplierInterface;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\SearchResultApplierInterface;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Collection;
use Magento\Framework\EntityManager\MetadataPool;

/**
 * Resolve specific attributes for search criteria.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var StockStatusFilterInterface
     */
    private $stockStatusFilter;

    /**
     * @var StockStatusApplierInterface
     */
    private $stockStatusApplier;

    /**
     * @param Collection $collection
     * @param SearchResultInterface $searchResult
     * @param int $size
     * @param int $currentPage
     * @param ScopeConfigInterface|null $scopeConfig
     * @param MetadataPool|null $metadataPool
     * @param StockStatusFilterInterface|null $stockStatusFilter
     * @param StockStatusApplierInterface|null $stockStatusApplier
     */
    public function __construct(
        Collection $collection,
        SearchResultInterface $searchResult,
        int $size,
        int $currentPage,
        ?ScopeConfigInterface $scopeConfig = null,
        ?MetadataPool $metadataPool = null,
        ?StockStatusFilterInterface $stockStatusFilter = null,
        ?StockStatusApplierInterface $stockStatusApplier = null
    ) {
        $this->collection = $collection;
        $this->searchResult = $searchResult;
        $this->size = $size;
        $this->currentPage = $currentPage;
        $this->scopeConfig = $scopeConfig ?? ObjectManager::getInstance()->get(ScopeConfigInterface::class);
        $this->metadataPool = $metadataPool ?? ObjectManager::getInstance()->get(MetadataPool::class);
        $this->stockStatusFilter = $stockStatusFilter
            ?? ObjectManager::getInstance()->get(StockStatusFilterInterface::class);
        $this->stockStatusApplier = $stockStatusApplier
            ?? ObjectManager::getInstance()->get(StockStatusApplierInterface::class);
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

        $ids = [];
        $items = $this->sliceItems($this->searchResult->getItems(), $this->size, $this->currentPage);
        foreach ($items as $item) {
            $ids[] = (int)$item->getId();
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
}
