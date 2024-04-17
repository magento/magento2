<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Ui\DataProvider\Product;

use Magento\CatalogSearch\Model\ResourceModel\Fulltext\BackendCollection;
use Magento\Framework\Data\Collection;
use Magento\Ui\DataProvider\AddFilterToCollectionInterface;

/**
 * Class AddFulltextFilterToCollection
 */
class AddFulltextFilterToCollection implements AddFilterToCollectionInterface
{
    private BackendCollection $backendCollection;

    /**
     * @param BackendCollection $backendCollection
     */
    public function __construct(
        BackendCollection $backendCollection
    ) {
        $this->backendCollection = $backendCollection;
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
            $this->backendCollection->addSearchFilter($condition['fulltext']);
            $productIds = $this->backendCollection->load()->getAllIds();
            if (empty($productIds)) {
                //add dummy id to prevent returning full unfiltered collection
                $productIds = -1;
            }
            $collection->addIdFilter($productIds);
        }
    }
}
