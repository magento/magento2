<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Observer;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Controller\Adminhtml\Product\Save;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

/**
 * Process source items during product saving via controller
 */
final class ProcessSourceItemsObserver implements ObserverInterface
{
    /**
     * @var SourceItemsProcessor
     */
    private $sourceItemsProcessor;

    /**
     * @param SourceItemsProcessor $sourceItemsProcessor
     */
    public function __construct(SourceItemsProcessor $sourceItemsProcessor)
    {
        $this->sourceItemsProcessor = $sourceItemsProcessor;
    }

    /**
     * Process source items during product saving via controller
     *
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        /** @var ProductInterface $product */
        $product = $observer->getEvent()->getProduct();
        /** @var Save $controller */
        $controller = $observer->getEvent()->getController();

        $this->sourceItemsProcessor->process(
            $product->getSku(),
            $controller->getRequest()->getParam('sources', [])
        );
    }
}
