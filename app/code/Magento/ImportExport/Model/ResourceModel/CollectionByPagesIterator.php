<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Model\ResourceModel;

/**
 * Collection by pages iterator
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class CollectionByPagesIterator
{
    /**
     * Load collection page by page and apply callbacks to each collection item
     *
     * @param \Magento\Framework\Data\Collection\AbstractDb $collection Collection to load page by page
     * @param int $pageSize Number of items to fetch from db in one query
     * @param array $callbacks Array of callbacks which should be applied to each collection item
     * @return void
     */
    public function iterate(\Magento\Framework\Data\Collection\AbstractDb $collection, $pageSize, array $callbacks)
    {
        /** @var $paginatedCollection \Magento\Framework\Data\Collection\AbstractDb */
        $paginatedCollection = null;
        $pageNumber = 1;
        do {
            $paginatedCollection = clone $collection;
            $paginatedCollection->clear();

            $paginatedCollection->setPageSize($pageSize)->setCurPage($pageNumber);

            if ($paginatedCollection->count() > 0) {
                foreach ($paginatedCollection as $item) {
                    foreach ($callbacks as $callback) {
                        call_user_func($callback, $item);
                    }
                }
            }

            $pageNumber++;
        } while ($pageNumber <= $paginatedCollection->getLastPageNumber());

        $paginatedCollection->clear();
        unset($paginatedCollection);
    }
}
