<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\CatalogSearch\Ui\DataProvider\Product;

use Magento\CatalogSearch\Model\ResourceModel\Search\Collection as SearchCollection;
use Magento\Framework\Data\Collection;
use Magento\Ui\DataProvider\AddFilterToCollectionInterface;

/**
 * Restricts a product collection to the entities matching an admin grid keyword
 */
class AddFulltextFilterToCollection implements AddFilterToCollectionInterface
{
    /**
     * @var SearchCollection
     */
    private $searchCollection;

    /**
     * @param SearchCollection $searchCollection
     */
    public function __construct(SearchCollection $searchCollection)
    {
        $this->searchCollection = $searchCollection;
    }

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function addFilter(Collection $collection, $field, $condition = null)
    {
        /** @var $collection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        if (isset($condition['fulltext']) && (string)$condition['fulltext'] !== '') {
            $linkField = $this->searchCollection->getEntity()->getLinkField();
            $collection->getSelect()->joinInner(
                ['search_result' => $this->searchCollection->getBackendSearchEntityIdsSelect($condition['fulltext'])],
                "search_result.{$linkField} = e.{$linkField}",
                []
            );
        }
    }
}
