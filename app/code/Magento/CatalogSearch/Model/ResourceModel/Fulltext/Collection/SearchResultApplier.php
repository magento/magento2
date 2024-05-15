<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection;

use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Data\Collection;

/**
 * Resolve specific attributes for search criteria.
 *
 * @deprecated mysql search engine has been removed
 * @see \Magento\Elasticsearch\Model\ResourceModel\Fulltext\Collection\SearchResultApplier
 */
class SearchResultApplier implements SearchResultApplierInterface
{
    /**
     * @var Collection
     */
    private $collection;

    /**
     * @var SearchResultInterface
     */
    private $searchResult;

    /**
     * @var array
     */
    private $orders;

    /**
     * @param Collection $collection
     * @param SearchResultInterface $searchResult
     * @param array $orders
     */
    public function __construct(
        Collection $collection,
        SearchResultInterface $searchResult,
        array $orders
    ) {
        $this->collection = $collection;
        $this->searchResult = $searchResult;
        $this->orders = $orders;
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
        foreach ($this->searchResult->getItems() as $item) {
            $ids[] = (int)$item->getId();
        }

        $orderList = implode(',', $ids);
        $this->collection->getSelect()->where('e.entity_id IN (?)', $ids);

        if (isset($this->orders['relevance'])) {
            $this->collection->getSelect()
                ->reset(\Magento\Framework\DB\Select::ORDER)
                ->order(new \Magento\Framework\DB\Sql\Expression("FIELD(e.entity_id, $orderList)"));
        }
    }
}
