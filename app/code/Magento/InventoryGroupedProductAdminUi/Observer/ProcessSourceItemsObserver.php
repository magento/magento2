<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryGroupedProductAdminUi\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\GroupedProduct\Model\Product\Type\Grouped as GroupedProductType;
use Magento\InventoryCatalogAdminUi\Observer\SourceItemsProcessor;

/**
 * Process source items for grouped associated products during product saving via controller.
 */
class ProcessSourceItemsObserver implements ObserverInterface
{
    /**
     * @var SourceItemsProcessor
     */
    private $sourceItemsProcessor;

    /**
     * @param SourceItemsProcessor $sourceItemsProcessor
     */
    public function __construct(
        SourceItemsProcessor $sourceItemsProcessor
    ) {
        $this->sourceItemsProcessor = $sourceItemsProcessor;
    }

    /**
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer): void
    {
        $product = $observer->getEvent()->getProduct();
        if ($product->getTypeId() !== GroupedProductType::TYPE_CODE) {
            return;
        }
        $controller = $observer->getEvent()->getController();
        $postAssociatedProductsData = $controller->getRequest()->getParam('links', [])['associated'] ?? [];
        $sourcesDataBySku = $this->associatePostSourceDataWithSku($postAssociatedProductsData);
        $linkedProducts = $product->getProductLinks();

        if (!empty($linkedProducts) && !empty($sourcesDataBySku)) {
            foreach ($linkedProducts as $linkedProduct) {
                $sku = $linkedProduct['linked_product_sku'];
                if ($linkedProduct['link_type'] === 'associated'
                    && isset($sourcesDataBySku[$sku])
                ) {
                    $this->sourceItemsProcessor->process($sku, $sourcesDataBySku[$sku]);
                }
            }
        }
    }

    /**
     * Associate linked product sku and source item data.
     *
     * @param array $postAssociatedProductsData
     * @return array
     */
    private function associatePostSourceDataWithSku(array $postAssociatedProductsData): array
    {
        $result = [];

        foreach ($postAssociatedProductsData as $linkedProductData) {
            if (isset($linkedProductData['sku'])
                && isset($linkedProductData['quantity_per_source'])
                && !empty($linkedProductData['quantity_per_source'])
            ) {
                $result[$linkedProductData['sku']] = $linkedProductData['quantity_per_source'];
            }
        }

        return $result;
    }
}
