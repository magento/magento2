<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model;

use Magento\Store\Api\StoreManagementInterface;
use Magento\Store\Model\ResourceModel\Store\CollectionFactory;

/**
 * @api
 * @since 2.0.0
 */
class StoreManagement implements StoreManagementInterface
{
    /**
     * @var CollectionFactory
     * @since 2.0.0
     */
    protected $storesFactory;

    /**
     * @param CollectionFactory $storesFactory
     * @since 2.0.0
     */
    public function __construct(CollectionFactory $storesFactory)
    {
        $this->storesFactory = $storesFactory;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getCount()
    {
        $stores = $this->storesFactory->create();
        /** @var \Magento\Store\Model\ResourceModel\Store\Collection $stores */
        $stores->setWithoutDefaultFilter();
        return $stores->getSize();
    }
}
