<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Ui\DataProvider\Product;

use Magento\CatalogSearch\Model\ResourceModel\Search\Collection as SearchCollection;
use Magento\Framework\Data\Collection;
use Magento\Ui\DataProvider\AddFilterToCollectionInterface;

/**
 * Class AddFulltextFilterToCollection
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
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function addFilter(Collection $collection, $field, $condition = null)
    {
        /** @var $collection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        if (isset($condition['fulltext']) && (string)$condition['fulltext'] !== '') {
            $this->searchCollection->addBackendSearchFilter($condition['fulltext']);
            $productIds = $this->searchCollection->load()->getAllIds();
            if (empty($productIds)) {
                //add dummy id to prevent returning full unfiltered collection
                $productIds = -1;
            }
            $collection->addIdFilter($productIds);
        }
    }
}
