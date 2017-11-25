<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Observer;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Controller\Adminhtml\Product\Save;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\InventoryCatalog\Api\DefaultSourceProviderInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory;

/**
 * Save source product relations during product persistence via controller
 *
 * This needs to be handled in dedicated observer, because there is no pre-defined way of making several API calls for
 * Form submission handling
 */
class ProcessSourceItemsObserver implements ObserverInterface
{
    /**
     * @var SourceItemsProcessor
     */
    private $sourceItemsProcessor;
    /**
     * @var DefaultSourceProviderInterface
     */
    private $defaultSourceProvider;
    /**
     * @var SourceItemInterfaceFactory
     */
    private $sourceItemInterfaceFactory;
    /**
     * @var SourceItemsSaveInterface
     */
    private $sourceItemsSave;

    /**
     * @param SourceItemsProcessor $sourceItemsProcessor
     * @param DefaultSourceProviderInterface $defaultSourceProvider
     * @param SourceItemInterfaceFactory $sourceItemInterfaceFactory
     * @param SourceItemsSaveInterface $sourceItemsSave
     */
    public function __construct(
        SourceItemsProcessor $sourceItemsProcessor,
        DefaultSourceProviderInterface $defaultSourceProvider,
        SourceItemInterfaceFactory $sourceItemInterfaceFactory,
        SourceItemsSaveInterface $sourceItemsSave
    ) {
        $this->sourceItemsProcessor = $sourceItemsProcessor;
        $this->defaultSourceProvider = $defaultSourceProvider;
        $this->sourceItemInterfaceFactory = $sourceItemInterfaceFactory;
        $this->sourceItemsSave = $sourceItemsSave;
    }

    /**
     * Process source items during product saving via controller
     *
     * @param EventObserver $observer
     *
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
            ? $sources['assigned_sources'] : [];

        $this->sourceItemsProcessor->process(
            $product->getSku(),
            $assignedSources
        );

        $this->updateDefaultSourceQty($controller);
    }

    /**
     * @param $controller Save
     * @return void
     */
    private function updateDefaultSourceQty($controller)
    {
        $productParams = $controller->getRequest()->getParam('product');

        $sku = $productParams['sku'];
        $qtyAndStockStatus = $productParams['quantity_and_stock_status'];
        $qty = $qtyAndStockStatus['qty'];
        $stockStatus = $qtyAndStockStatus['is_in_stock'];
        $defaultSourceId = $this->defaultSourceProvider->getId();

        /** @var  $sourceItem SourceItemInterface */
        $sourceItem = $this->sourceItemInterfaceFactory->create([
            SourceItemInterface::SKU => $sku,
            SourceItemInterface::QUANTITY => $qty,
            SourceItemInterface::STATUS => $stockStatus,
            SourceItemInterface::SOURCE_ID => $defaultSourceId
        ]);

        $this->sourceItemsSave->execute([$sourceItem]);
    }
}
