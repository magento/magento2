<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class \Magento\Catalog\Observer\CatalogCheckIsUsingStaticUrlsAllowedObserver
 *
 * @since 2.0.0
 */
class CatalogCheckIsUsingStaticUrlsAllowedObserver implements ObserverInterface
{
    /**
     * Catalog data
     *
     * @var \Magento\Catalog\Helper\Data
     * @since 2.0.0
     */
    protected $catalogData;

    /**
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @since 2.0.0
     */
    public function __construct(\Magento\Catalog\Helper\Data $catalogData)
    {
        $this->catalogData = $catalogData;
    }

    /**
     * Checking whether the using static urls in WYSIWYG allowed event
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @since 2.0.0
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $storeId = $observer->getEvent()->getData('store_id');
        $result = $observer->getEvent()->getData('result');
        $result->isAllowed = $this->catalogData->setStoreId($storeId)->isUsingStaticUrlsAllowed();
    }
}
