<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfiguration\Observer;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Controller\Adminhtml\Product\Save;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

/**
 * Save source relations (configuration) during product persistence via controller
 *
 * This needs to be handled in dedicated observer, because there is no pre-defined way of making several API calls for
 * Form submission handling
 */
class ProcessSourceItemsConfigurationObserver implements ObserverInterface
{
    /**
     * @var SourceItemsConfigurationProcessor
     */
    private $sourceItemsConfigurationProcessor;

    /**
     * ProcessSourceItemsConfigurationObserver constructor.
     * @param SourceItemsConfigurationProcessor $processSourceItemsConfigurationObserver
     */
    public function __construct(
        SourceItemsConfigurationProcessor $processSourceItemsConfigurationObserver
    ) {
        $this->sourceItemsConfigurationProcessor = $processSourceItemsConfigurationObserver;
    }

    /**
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        /** @var ProductInterface $product */
        $product = $observer->getEvent()->getProduct();
        /** @var Save $controller */
        $controller = $observer->getEvent()->getController();

        $sources = $controller->getRequest()->getParam('sources', []);
        $assignedSources = isset($sources['assigned_sources']) && is_array($sources['assigned_sources'])
            ? $sources['assigned_sources']
            : [];

        $this->sourceItemsConfigurationProcessor->process($product->getSku(), $assignedSources);
    }
}