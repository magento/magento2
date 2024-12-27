<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Observer;

use Magento\Catalog\Model\Attribute\Backend\WebsiteSpecific\Scheduler;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\Store;

class SynchronizeWebsiteAttributesOnStoreChange implements ObserverInterface
{
    /**
     * @param Scheduler $scheduler
     */
    public function __construct(
        private Scheduler $scheduler
    ) {
    }

    /**
     * @inheritdoc
     */
    public function execute(Observer $observer)
    {
        $store = $observer->getData('data_object');
        if (!$store instanceof Store) {
            return;
        }

        if (!$store->hasDataChanges()) {
            return;
        }

        $isWebsiteIdChanged = $store->getOrigData('website_id') != $store->getWebsiteId();
        $isStoreNew = $store->isObjectNew();

        if ($isWebsiteIdChanged || $isStoreNew) {
            $this->scheduler->execute((int) $store->getId());
        }
    }
}
