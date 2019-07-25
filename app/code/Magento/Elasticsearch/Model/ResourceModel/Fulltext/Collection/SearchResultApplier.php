<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Elasticsearch\Model\ResourceModel\Fulltext\Collection;

use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\SearchResultApplierInterface;
use Magento\Framework\Data\Collection;
use Magento\Framework\Api\Search\SearchResultInterface;

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
     * @param Collection $collection
     * @param SearchResultInterface $searchResult
     */
    public function __construct(
        Collection $collection,
        SearchResultInterface $searchResult
    ) {
        $this->collection = $collection;
        $this->searchResult = $searchResult;
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
        $this->collection->setPageSize(null);
        $this->collection->getSelect()->where('e.entity_id IN (?)', $ids);
        $orderList = join(',', $ids);
        $this->collection->getSelect()->reset(\Magento\Framework\DB\Select::ORDER);
        $this->collection->getSelect()->order("FIELD(e.entity_id,$orderList)");
    }
}
