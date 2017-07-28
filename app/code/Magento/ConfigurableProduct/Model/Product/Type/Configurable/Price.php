<?php
/**
 * Product type price model
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model\Product\Type\Configurable;

/**
 * Class \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Price
 *
 * @since 2.0.0
 */
class Price extends \Magento\Catalog\Model\Product\Type\Price
{
    /**
     * Get product final price
     *
     * @param   float $qty
     * @param   \Magento\Catalog\Model\Product $product
     * @return  float
     * @since 2.0.0
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
     * @since 2.0.0
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
