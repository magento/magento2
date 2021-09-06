<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Block\Plugin;

use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class ProductView
{
    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @param StockRegistryInterface $stockRegistry
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        StockRegistryInterface $stockRegistry,
        Session $checkoutSession
    ) {
        $this->stockRegistry = $stockRegistry;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param \Magento\Catalog\Block\Product\View $block
     * @param array $validators
     *
     * @return array
     */
    public function afterGetQuantityValidators(
        \Magento\Catalog\Block\Product\View $block,
        array $validators
    ): array {
        $stockItem = $this->stockRegistry->getStockItem(
            $block->getProduct()->getId(),
            $block->getProduct()->getStore()->getWebsiteId()
        );

        $params = [];

        $params['minAllowed'] = (float)$stockItem->getMinSaleQty();

        if ($stockItem->getMaxSaleQty()) {
            $params['maxAllowed'] = (float)$stockItem->getMaxSaleQty();
            $params['cartQty'] = $this->getCartQty($block->getProduct());
        }

        if ($stockItem->getQtyIncrements() > 0) {
            $params['qtyIncrements'] = (float)$stockItem->getQtyIncrements();
            $params['cartQty'] = $this->getCartQty($block->getProduct());
        }

        $validators['validate-item-quantity'] = $params;

        return $validators;
    }

    /**
     * Check current cart Qty of Product.
     *
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return float
     */
    private function getCartQty(\Magento\Catalog\Model\Product $product): float
    {
        if ($this->checkoutSession->hasQuote()) {
            try {
                if ($item = $this->checkoutSession->getQuote()->getItemByProduct($product)) {
                    return $item->getQty();
                }
            } catch (NoSuchEntityException | LocalizedException $e) {
                return 0;
            }
        }

        return 0;
    }
}
