<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Observer;

use Magento\Framework\Event\ObserverInterface;

class LoadProductOptionsObserver implements ObserverInterface
{
    /**
     * Add price index data for catalog product collection
     * only for front end
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $collection = $observer->getEvent()->getCollection();
        /* @var $collection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $collection->addPriceData();

        return $this;
    }
}
