<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Ui\DataProvider\Product\Form\Modifier\Data;

use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Catalog\Model\Product;

class ProductStockDataProvider
{
    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @param StockRegistryInterface $stockRegistry
     */
    public function __construct(StockRegistryInterface $stockRegistry)
    {
        $this->stockRegistry = $stockRegistry;
    }

    /**
     * @param Product $product
     *
     * @return array
     */
    public function execute(Product $product): array
    {
        $productId = $product->getId();
        $websiteId = $product->getStore()->getWebsiteId();

        $qty = $this->stockRegistry->getStockItem($productId, $websiteId)->getQty();

        return [$qty];
    }
}
