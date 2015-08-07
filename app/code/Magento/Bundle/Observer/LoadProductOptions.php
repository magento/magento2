<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Observer;

class LoadProductOptions
{
    /**
     * Add price index data for catalog product collection
     * only for front end
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function invoke($observer)
    {
        $collection = $observer->getEvent()->getCollection();
        /* @var $collection \Magento\Catalog\Model\Resource\Product\Collection */
        $collection->addPriceData();

        return $this;
    }
}
