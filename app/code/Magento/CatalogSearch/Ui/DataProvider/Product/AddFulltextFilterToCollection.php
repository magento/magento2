<?php

namespace Magento\CatalogSearch\Ui\DataProvider\Product;

use Magento\Framework\Data\Collection;
use Magento\CatalogSearch\Model\ResourceModel\Search\Collection as SearchCollection;
use Magento\Ui\DataProvider\AddFilterToCollectionInterface;

/**
 * Class AddFulltextFilterToCollection
 */
class AddFulltextFilterToCollection implements AddFilterToCollectionInterface
{
    /*
     * Search Collection
     *
     * @var SearchCollection
     */
    protected $searchCollection;

    /*
     * Construct
     *
     * @param SearchCollection $searchCollection
     */
    public function __construct(SearchCollection $searchCollection)
    {
        $this->searchCollection = $searchCollection;
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings("unused")
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
