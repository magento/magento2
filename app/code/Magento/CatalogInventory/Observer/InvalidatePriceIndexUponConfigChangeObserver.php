<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\CatalogInventory\Model\Configuration;
use Magento\Catalog\Model\Indexer\Product\Price\Processor;

/**
 * Catalog inventory config changes module observer.
 */
class InvalidatePriceIndexUponConfigChangeObserver implements ObserverInterface
{
    /**
     * @var Processor
     */
    private $priceIndexProcessor;

    /**
     * @param Processor $priceIndexProcessor
     */
    public function __construct(Processor $priceIndexProcessor)
    {
        $this->priceIndexProcessor = $priceIndexProcessor;
    }

    /**
     * Invalidate product price index on catalog inventory config changes.
     *
     * @param Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(Observer $observer)
    {
        $changedPaths = (array) $observer->getEvent()->getChangedPaths();

        if (\in_array(Configuration::XML_PATH_SHOW_OUT_OF_STOCK, $changedPaths, true)) {
            $priceIndexer = $this->priceIndexProcessor->getIndexer();
            $priceIndexer->invalidate();
        }
    }
}
