<?php
/**
 * Grouped product price model
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProduct\Model\Product\Type\Grouped;

class Price extends \Magento\Catalog\Model\Product\Type\Price
{
    /**
     * Returns product final price depending on options chosen
     *
     * @param   float $qty
     * @param   \Magento\Catalog\Model\Product $product
     * @return  float
     */
    public function getFinalPrice($qty, $product)
    {
        if ($qty === null && $product->getCalculatedFinalPrice() !== null) {
            return $product->getCalculatedFinalPrice();
        }

        $finalPrice = parent::getFinalPrice($qty, $product);
        if ($product->hasCustomOptions()) {
            /* @var $typeInstance \Magento\GroupedProduct\Model\Product\Type\Grouped */
            $typeInstance = $product->getTypeInstance();
            $associatedProducts = $typeInstance->setStoreFilter(
                $product->getStore(),
                $product
            )->getAssociatedProducts(
                $product
            );
            foreach ($associatedProducts as $childProduct) {
                /* @var $childProduct \Magento\Catalog\Model\Product */
                $option = $product->getCustomOption('associated_product_' . $childProduct->getId());
                if (!$option) {
                    continue;
                }
                $childQty = $option->getValue();
                if (!$childQty) {
                    continue;
                }
                $finalPrice += $childProduct->getFinalPrice($childQty) * $childQty;
            }
        }

        $product->setFinalPrice($finalPrice);

        return max(0, $product->getData('final_price'));
    }
}
