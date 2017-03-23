<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GroupedProduct\Pricing\Price;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Price\FinalPriceInterface;

/**
 * Final price model
 */
class FinalPrice extends \Magento\Catalog\Pricing\Price\FinalPrice implements FinalPriceInterface
{
    /**
     * Price type final
     */
    const PRICE_CODE = 'final_price';

    /**
     * @var Product
     */
    protected $minProduct;

    /**
     * Return minimal product price
     *
     * @return float
     */
    public function getValue()
    {
        return $this->getMinProduct()->getPriceInfo()->getPrice(FinalPrice::PRICE_CODE)->getValue();
    }

    /**
     * Returns product with minimal price
     *
     * @return Product
     */
    public function getMinProduct()
    {
        if (null === $this->minProduct) {
            $products = $this->product->getTypeInstance()->getAssociatedProducts($this->product);
            $minPrice = null;
            foreach ($products as $item) {
                $product = clone $item;
                $product->setQty(\Magento\Framework\Pricing\PriceInfoInterface::PRODUCT_QUANTITY_DEFAULT);
                $price = $product->getPriceInfo()
                    ->getPrice(FinalPrice::PRICE_CODE)
                    ->getValue();
                if (($price !== false) && ($price <= ($minPrice === null ? $price : $minPrice))) {
                    $this->minProduct = $product;
                    $minPrice = $price;
                }
            }
        }
        return $this->minProduct;
    }
}
