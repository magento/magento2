<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Bundle\Model\Product;

use Magento\Catalog\Model\Product;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Get original price for bundle products
 */
class OriginalPrice
{
    /**
     * @param Json $serializer
     */
    public function __construct(private readonly Json $serializer)
    {
    }

    /**
     * Get Original Total price for Bundle items
     *
     * @param Product $product
     * @return float
     */
    public function getTotalBundleItemsOriginalPrice(Product $product): float
    {
        $price = 0.0;

        if (!$product->hasCustomOptions()) {
            return $price;
        }

        $selectionIds = $this->getBundleSelectionIds($product);

        if (empty($selectionIds)) {
            return $price;
        }

        $selections = $product->getTypeInstance()->getSelectionsByIds($selectionIds, $product);
        foreach ($selections->getItems() as $selection) {
            if (!$selection->isSalable()) {
                continue;
            }

            $selectionQty = $product->getCustomOption('selection_qty_' . $selection->getSelectionId());
            if ($selectionQty) {
                $price += $this->getSelectionOriginalTotalPrice(
                    $product,
                    $selection,
                    (float) $selectionQty->getValue()
                );
            }
        }

        return $price;
    }

    /**
     * Calculate total original price of selection
     *
     * @param Product $bundleProduct
     * @param Product $selectionProduct
     * @param float $selectionQty
     *
     * @return float
     */
    private function getSelectionOriginalTotalPrice(
        Product $bundleProduct,
        Product $selectionProduct,
        float $selectionQty
    ): float {
        $price = $this->getSelectionOriginalPrice($bundleProduct, $selectionProduct);

        return $price * $selectionQty;
    }

    /**
     * Calculate the original price of selection
     *
     * @param Product $bundleProduct
     * @param Product $selectionProduct
     *
     * @return float
     */
    public function getSelectionOriginalPrice(Product $bundleProduct, Product $selectionProduct): float
    {
        if ($bundleProduct->getPriceType() == Price::PRICE_TYPE_DYNAMIC) {
            return (float) $selectionProduct->getPrice();
        }
        if ($selectionProduct->getSelectionPriceType()) {
            // percent
            return $bundleProduct->getPrice() * ($selectionProduct->getSelectionPriceValue() / 100);
        }

        // fixed
        return (float) $selectionProduct->getSelectionPriceValue();
    }

    /**
     * Retrieve array of bundle selection IDs
     *
     * @param Product $product
     * @return array
     */
    private function getBundleSelectionIds(Product $product): array
    {
        $customOption = $product->getCustomOption('bundle_selection_ids');
        if ($customOption) {
            $selectionIds = $this->serializer->unserialize($customOption->getValue());
            if (is_array($selectionIds)) {
                return $selectionIds;
            }
        }
        return [];
    }
}
