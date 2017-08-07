<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Observer;

use Magento\Catalog\Model\ResourceModel\Attribute\WebsiteAttributesSynchronizer;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\Store;

/**
 * Class SynchronizeWebsiteAttributesOnStoreChange
 * @package Magento\Catalog\Observer
 * @since 2.2.0
 */
class SynchronizeWebsiteAttributesOnStoreChange implements ObserverInterface
{
    /**
     * @var WebsiteAttributesSynchronizer
     * @since 2.2.0
     */
    private $synchronizer;

    /**
     * SynchronizeWebsiteAttributesOnStoreChange constructor.
     * @param WebsiteAttributesSynchronizer $synchronizer
     * @since 2.2.0
     */
    public function __construct(WebsiteAttributesSynchronizer $synchronizer)
    {
        $this->synchronizer = $synchronizer;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
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
            $this->synchronizer->scheduleSynchronization();
        }
    }
}
