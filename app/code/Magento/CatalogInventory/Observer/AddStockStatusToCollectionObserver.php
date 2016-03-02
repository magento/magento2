<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

/**
 * Catalog inventory module observer
 */
class AddStockStatusToCollectionObserver implements ObserverInterface
{

    /**
     * @var string[]
     */
    private $processedCollections = [];

    /**
     * Add information about product stock status to collection
     * Used in for product collection after load
     *
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Collection\AbstractCollection $productCollection */
        $productCollection = $observer->getEvent()->getCollection();

        $collectionHash = spl_object_hash($productCollection);
        if  (!in_array($collectionHash, $this->processedCollections)) {
            $productCollection->getSelect()
                ->join(
                    ['css' => $productCollection->getResource()->getTable('cataloginventory_stock_status')],
                    'main_table.entity_id = css.entity_id AND css.website_id = 1 AND stock_id = 1',
                    ['is_salable' => 'css.stock_status']
                );
            $this->processedCollections[] = $collectionHash;
        }
    }
}
