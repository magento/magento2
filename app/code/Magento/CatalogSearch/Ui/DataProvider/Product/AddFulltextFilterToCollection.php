<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD
=======

>>>>>>> upstream/2.2-develop
namespace Magento\CatalogSearch\Ui\DataProvider\Product;

use Magento\Framework\Data\Collection;
use Magento\CatalogSearch\Model\ResourceModel\Search\Collection as SearchCollection;
use Magento\Ui\DataProvider\AddFilterToCollectionInterface;

/**
<<<<<<< HEAD
 * Class AddFulltextFilterToCollection
=======
 * Adds FullText search to Product Data Provider
>>>>>>> upstream/2.2-develop
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
