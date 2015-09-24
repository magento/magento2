<?php
/**
 * Product type price model
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model\Product\Type\Configurable;

use Magento\Framework\Pricing\PriceCurrencyInterface;

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
        if ($product->getCustomOption('simple_product')) {
            $simpleProduct = $product->getCustomOption('simple_product')->getProduct();
            $product->setSelectedConfigurableOption($simpleProduct);
            $priceInfo = $simpleProduct->getPriceInfo();
        } else {
            $priceInfo = $product->getPriceInfo();
        }
        $finalPrice = $priceInfo->getPrice('final_price')->getAmount()->getValue();
        $finalPrice = $this->_applyOptionsPrice($product, $qty, $finalPrice);
        $finalPrice = max(0, $finalPrice);
        $product->setFinalPrice($finalPrice);

        return $finalPrice;
    }
}
