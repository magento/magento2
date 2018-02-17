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
use Magento\Framework\Exception\InputException;
use Magento\Inventory\Model\IsSourceItemsManagementAllowedForProductTypeInterface;
use Magento\InventoryCatalog\Model\IsSingleSourceModeInterface;

/**
 * Save source product relations during product persistence via controller
 *
 * This needs to be handled in dedicated observer, because there is no pre-defined way of making several API calls for
 * Form submission handling
 */
class ProcessSourceItemsObserver implements ObserverInterface
{
    /**
     * @var IsSingleSourceModeInterface
     */
    private $isSingleSourceMode;

    /**
     * @var IsSourceItemsManagementAllowedForProductTypeInterface
     */
    private $isSourceItemsManagementAllowedForProductType;

    /**
     * @var SourceItemsProcessor
     */
    private $sourceItemsProcessor;

    /**
     * @param IsSourceItemsManagementAllowedForProductTypeInterface $isSourceItemsManagementAllowedForProductType
     * @param SourceItemsProcessor $sourceItemsProcessor
     */
    public function __construct(
        IsSourceItemsManagementAllowedForProductTypeInterface $isSourceItemsManagementAllowedForProductType,
        SourceItemsProcessor $sourceItemsProcessor,
        IsSingleSourceModeInterface $isSingleSourceMode
    ) {
        $this->isSourceItemsManagementAllowedForProductType = $isSourceItemsManagementAllowedForProductType;
        $this->sourceItemsProcessor = $sourceItemsProcessor;
        $this->isSingleSourceMode = $isSingleSourceMode;
    }

    /**
     * Process source items during product saving via controller
     *
     * @param EventObserver $observer
     * @throws InputException
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        /** @var ProductInterface $product */
        $product = $observer->getEvent()->getProduct();
        if ($this->isSourceItemsManagementAllowedForProductType->execute($product->getTypeId()) === false) {
            return;
        }

        /** @var Save $controller */
        $controller = $observer->getEvent()->getController();

        if (!$this->isSingleSourceMode->execute()) {
            $sources = $controller->getRequest()->getParam('sources', []);
            $assignedSources = isset($sources['assigned_sources']) && is_array($sources['assigned_sources'])
                ? $sources['assigned_sources'] : [];
        } else { // Process legacy stock status fields values
            $productData = $controller->getRequest()->getParam('product', []);
            // TODO: We may move this logic into a separate method
            $assignedSources[0] = [
                'source_code' => 'default', // TODO: get default source code
                'name' => 'default source', // TODO: get default source name
                'position' => '1', // ???
                'record_id' => 'default', // ???
                'status' => $productData['quantity_and_stock_status']['is_in_stock'],
                'quantity' => $productData['quantity_and_stock_status']['qty'],
                'notify_stock_qty' => $productData['stock_data']['notify_stock_qty'],
                'notify_stock_qty_use_default' => $productData['stock_data']['use_config_notify_stock_qty']
            ];
        }

        $this->sourceItemsProcessor->process(
            $product->getSku(),
            $assignedSources
        );
    }
}
