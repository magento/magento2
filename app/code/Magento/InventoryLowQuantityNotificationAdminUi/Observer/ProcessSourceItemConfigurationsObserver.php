<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLowQuantityNotificationAdminUi\Observer;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Controller\Adminhtml\Product\Save;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface;
use Magento\InventoryCatalogApi\Model\IsSingleSourceModeInterface;
use Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface;
use Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForProductTypeInterface;
use Magento\InventoryLowQuantityNotificationAdminUi\Model\SourceItemsConfigurationProcessor;

/**
 * Save source relations (configuration) during product persistence via controller
 *
 * This needs to be handled in dedicated observer, because there is no pre-defined way of making several API calls for
 * Form submission handling
 */
class ProcessSourceItemConfigurationsObserver implements ObserverInterface
{
    /**
     * @var IsSourceItemManagementAllowedForProductTypeInterface
     */
    private $isSourceItemManagementAllowedForProductType;

    /**
     * @var SourceItemsConfigurationProcessor
     */
    private $sourceItemsConfigurationProcessor;

    /**
     * @var IsSingleSourceModeInterface
     */
    private $isSingleSourceMode;

    /**
     * @var DefaultSourceProviderInterface
     */
    private $defaultSourceProvider;

    /**
     * @param IsSourceItemManagementAllowedForProductTypeInterface $isSourceItemManagementAllowedForProductType
     * @param SourceItemsConfigurationProcessor $sourceItemsConfigurationProcessor
     */
    public function __construct(
        IsSourceItemManagementAllowedForProductTypeInterface $isSourceItemManagementAllowedForProductType,
        SourceItemsConfigurationProcessor $sourceItemsConfigurationProcessor,
        IsSingleSourceModeInterface $isSingleSourceMode,
        DefaultSourceProviderInterface $defaultSourceProvider
    ) {
        $this->isSourceItemManagementAllowedForProductType = $isSourceItemManagementAllowedForProductType;
        $this->sourceItemsConfigurationProcessor = $sourceItemsConfigurationProcessor;
        $this->isSingleSourceMode = $isSingleSourceMode;
        $this->defaultSourceProvider = $defaultSourceProvider;
    }

    /**
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        /** @var ProductInterface $product */
        $product = $observer->getEvent()->getProduct();
        if ($this->isSourceItemManagementAllowedForProductType->execute($product->getTypeId()) === false) {
            return;
        }

        /** @var Save $controller */
        $controller = $observer->getEvent()->getController();

        $assignedSources = [];
        if ($this->isSingleSourceMode->execute()) {
            $stockData = $controller->getRequest()->getParam('product', [])['stock_data'] ?? [];
            $assignedSources[] = [
                SourceItemInterface::SOURCE_CODE => $this->defaultSourceProvider->getCode(),
                StockItemConfigurationInterface::NOTIFY_STOCK_QTY =>
                    $stockData[StockItemConfigurationInterface::NOTIFY_STOCK_QTY] ?? 0,
                'notify_stock_qty_use_default' =>
                    $stockData[StockItemConfigurationInterface::USE_CONFIG_NOTIFY_STOCK_QTY] ?? 1,
            ];
        } else {
            $sources = $controller->getRequest()->getParam('sources', []);
            if (isset($sources['assigned_sources']) && is_array($sources['assigned_sources'])) {
                $assignedSources = $sources['assigned_sources'];
            }
        }

        $this->sourceItemsConfigurationProcessor->process($product->getSku(), $assignedSources);
    }
}
