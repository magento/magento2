<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfigurableProduct\Observer;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Controller\Adminhtml\Product\Save;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryCatalog\Observer\SourceItemsProcessor;
use Magento\InventoryLowQuantityNotification\Observer\SourceItemsConfigurationProcessor;

/**
 * Process source items for configurable children products during product saving via controller.
 */
class ProcessSourceItemsObserver implements ObserverInterface
{

    /**
     * @var SourceItemsProcessor
     */
    private $sourceItemsProcessor;

    /**
     * @var SourceItemsConfigurationProcessor
     */
    private $sourceItemsConfigurationProcessor;

    /**
     * @param SourceItemsProcessor $sourceItemsProcessor
     * @param SourceItemsConfigurationProcessor $sourceItemsConfigurationProcessor
     */
    public function __construct(
        SourceItemsProcessor $sourceItemsProcessor,
        SourceItemsConfigurationProcessor $sourceItemsConfigurationProcessor
    ) {
        $this->sourceItemsProcessor = $sourceItemsProcessor;
        $this->sourceItemsConfigurationProcessor = $sourceItemsConfigurationProcessor;
    }

    /**
     * @param EventObserver $observer
     *
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        /** @var ProductInterface $product */
        $product = $observer->getEvent()->getProduct();
        if ($product->getTypeId() !== Configurable::TYPE_CODE) {
            return;
        }
        /** @var Save $controller */
        $controller = $observer->getEvent()->getController();
        $configurableMatrix = $controller->getRequest()->getParam('configurable-matrix-serialized', []);

        if ($configurableMatrix != "") {
            $productsData = json_decode($configurableMatrix, true);
            foreach ($productsData as $productData) {
                $sku = $productData[ProductInterface::SKU];
                $sourceItems = $productData['qty'];

                $this->processSourceItems($sourceItems, $sku);
                $this->processSourceItemsConfiguration($sourceItems, $sku);
            }
        }
    }

    /**
     * @param array $sourceItems
     * @param string $productSku
     *
     * @return void
     */
    private function processSourceItems(array $sourceItems, string $productSku)
    {
        foreach ($sourceItems as $key => $sourceItem) {
            if (!isset($sourceItem[SourceItemInterface::STATUS])) {
                $sourceItems[$key][SourceItemInterface::STATUS] =
                    $sourceItems[$key][SourceItemInterface::QUANTITY] ? 1 : 0;
            }
        }

        $this->sourceItemsProcessor->process($productSku, $sourceItems);
    }

    /**
     * @param array $sourceItems
     * @param string $sku
     *
     * @return void
     */
    private function processSourceItemsConfiguration(array $sourceItems, string $sku)
    {
        foreach ($sourceItems as $key => $sourceItem) {
            if (isset($sourceItem['quantity_below_group'])) {
                $sourceItems[$key] = array_merge($sourceItems[$key], $sourceItem['quantity_below_group']);
            } else {
                unset($sourceItems[$key]);
            }
        }

        $this->sourceItemsConfigurationProcessor->process($sku, $sourceItems);
    }
}
