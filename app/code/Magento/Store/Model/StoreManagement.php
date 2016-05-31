<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model;

use Magento\Store\Api\StoreManagementInterface;
use Magento\Store\Model\ResourceModel\Store\CollectionFactory;

/**
 * @api
 */
class StoreManagement implements StoreManagementInterface
{
    /**
     * @var CollectionFactory
     */
    protected $storesFactory;

    /**
     * @param CollectionFactory $storesFactory
     */
    public function __construct(CollectionFactory $storesFactory)
    {
        $this->storesFactory = $storesFactory;
    }
    /**
     * {@inheritdoc}
     */
    public function getCount()
    {
        $stores = $this->storesFactory->create();
        /** @var \Magento\Store\Model\ResourceModel\Store\Collection $stores */
        $stores->setWithoutDefaultFilter();
        return $stores->getSize();
    }
}
