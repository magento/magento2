<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Observer;

use Magento\Catalog\Helper\Data;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class CatalogCheckIsUsingStaticUrlsAllowedObserver
 * @package Magento\Catalog\Observer
 */
class CatalogCheckIsUsingStaticUrlsAllowedObserver implements ObserverInterface
{
    /**
     * Catalog data
     *
     * @var Data
     */
    protected $catalogData;

    /**
     * @param Data $catalogData
     */
    public function __construct(Data $catalogData)
    {
        $this->catalogData = $catalogData;
    }

    /**
     * Checking whether the using static urls in WYSIWYG allowed event
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $storeId = $observer->getEvent()->getData('store_id');
        $result = $observer->getEvent()->getData('result');
        $result->isAllowed = $this->catalogData->setStoreId($storeId)->isUsingStaticUrlsAllowed();
    }
}
