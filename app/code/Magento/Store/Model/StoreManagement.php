<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model;

use Magento\Store\Api\StoreManagementInterface;
use Magento\Store\Model\ResourceModel\Store\Collection as StoreCollection;
use Magento\Store\Model\ResourceModel\Store\CollectionFactory;

/**
 * @api
 * @since 100.0.2
 */
class StoreManagement implements StoreManagementInterface
{
    /**
     * @param CollectionFactory $storesFactory
     */
    public function __construct(
        protected readonly CollectionFactory $storesFactory
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getCount()
    {
        $stores = $this->storesFactory->create();
        /** @var StoreCollection $stores */
        $stores->setWithoutDefaultFilter();
        return $stores->getSize();
    }
}
