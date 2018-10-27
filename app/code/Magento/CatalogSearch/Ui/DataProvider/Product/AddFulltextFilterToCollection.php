<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Ui\DataProvider\Product;

use Magento\Framework\Data\Collection;
use Magento\CatalogSearch\Model\ResourceModel\Search\Collection as SearchCollection;
use Magento\Ui\DataProvider\AddFilterToCollectionInterface;

/**
 * Adds FullText search to Product Data Provider
 */
class AddFulltextFilterToCollection implements AddFilterToCollectionInterface
{
    /**
     * Search Collection
     *
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
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function addFilter(Collection $collection, $field, $condition = null)
    {
        /** @var $collection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        if (isset($condition['fulltext']) && !empty($condition['fulltext'])) {
            $this->searchCollection->addBackendSearchFilter($condition['fulltext']);
            $productIds = $this->searchCollection->load()->getAllIds();
            $collection->addIdFilter($productIds);
        }
    }
}
