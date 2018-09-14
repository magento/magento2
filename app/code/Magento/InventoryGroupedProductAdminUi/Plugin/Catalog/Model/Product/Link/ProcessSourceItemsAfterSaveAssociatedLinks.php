<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryGroupedProductAdminUi\Plugin\Catalog\Model\Product\Link;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Link;
use Magento\GroupedProduct\Model\Product\Type\Grouped as GroupedProductType;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\GetSourceItemsBySkuInterface;
use Magento\InventoryCatalogAdminUi\Observer\SourceItemsProcessor;

/**
 * After save source links process child source items for reindex grouped product inventory.
 */
class ProcessSourceItemsAfterSaveAssociatedLinks
{
    /**
     * @var GetSourceItemsBySkuInterface
     */
    private $getSourceItemsBySku;

    /**
     * @var SourceItemsProcessor
     */
    private $sourceItemsProcessor;

    /**
     * @param GetSourceItemsBySkuInterface $getSourceItemsBySku
     * @param SourceItemsProcessor $sourceItemsProcessor
     */
    public function __construct(
        GetSourceItemsBySkuInterface $getSourceItemsBySku,
        SourceItemsProcessor $sourceItemsProcessor
    ) {
        $this->getSourceItemsBySku = $getSourceItemsBySku;
        $this->sourceItemsProcessor = $sourceItemsProcessor;
    }

    /**
     * @param Link $subject
     * @param Link $result
     * @param ProductInterface $product
     * @return Link
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSaveProductRelations(
        Link $subject,
        Link $result,
        ProductInterface $product
    ): Link {
        if ($product->getTypeId() !== GroupedProductType::TYPE_CODE) {
            return $result;
        }

        foreach ($product->getProductLinks() as $productLink) {
            if ($productLink->getLinkType() === 'associated') {
                $this->processSourceItemsForSku($productLink->getLinkedProductSku());
            }
        }

        return $result;
    }

    /**
     * Load source items data from assigned products and process this items.
     *
     * @param string $sku
     * @return void
     */
    private function processSourceItemsForSku(string $sku): void
    {
        $processData = [];

        foreach ($this->getSourceItemsBySku->execute($sku) as $sourceItem) {
            $processData[] = [
                SourceItemInterface::SOURCE_CODE => $sourceItem->getSourceCode(),
                SourceItemInterface::QUANTITY => $sourceItem->getQuantity(),
                SourceItemInterface::STATUS => $sourceItem->getStatus()
            ];
        }

        if (!empty($processData)) {
            $this->sourceItemsProcessor->process($sku, $processData);
        }
    }
}
