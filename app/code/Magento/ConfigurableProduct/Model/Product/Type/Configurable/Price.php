<?php
/**
 * Product type price model
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model\Product\Type\Configurable;

class Price extends \Magento\Catalog\Model\Product\Type\Price
{
    /**
     * Get product final price
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
        if ($product->getCustomOption('simple_product') && $product->getCustomOption('simple_product')->getProduct()) {
            $finalPrice = parent::getFinalPrice($qty, $product->getCustomOption('simple_product')->getProduct());
        } else {
            $priceInfo = $product->getPriceInfo();
            $finalPrice = $priceInfo->getPrice('final_price')->getAmount()->getValue();
        }
        $finalPrice = $this->_applyOptionsPrice($product, $qty, $finalPrice);
        $finalPrice = max(0, $finalPrice);
        $product->setFinalPrice($finalPrice);

        return $finalPrice;
    }

    /**
     * {@inheritdoc}
     */
    public function getPrice($product)
    {
        if (!empty($product)) {
            $simpleProductOption = $product->getCustomOption('simple_product');
            if (!empty($simpleProductOption)) {
                $simpleProduct = $simpleProductOption->getProduct();
                if (!empty($simpleProduct)) {
                    return $simpleProduct->getPrice();
                }
            }
        }
        return 0;
    }
}
