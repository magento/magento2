<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Review\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Review\Model\ResourceModel\Review\SummaryFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Append review summary to product list collection.
 */
class CatalogProductListCollectionAppendSummaryFieldsObserver implements ObserverInterface
{
    /**
     * Review model
     *
     * @var Summary
     */
    private $sumResourceFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param SummaryFactory $sumResourceFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        SummaryFactory $sumResourceFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->sumResourceFactory = $sumResourceFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * Append review summary to collection
     *
     * @param EventObserver $observer
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(EventObserver $observer)
    {
        $productCollection = $observer->getEvent()->getCollection();
        $this->sumResourceFactory->create()->appendSummaryFieldsToCollection(
            $productCollection,
            (int)$this->storeManager->getStore()->getId(),
            \Magento\Review\Model\Review::ENTITY_PRODUCT_CODE
        );

        return $this;
    }
}
