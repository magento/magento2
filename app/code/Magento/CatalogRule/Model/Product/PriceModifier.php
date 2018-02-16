<?php
/**
 * Catalog rule product price modifier.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Model\Product;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\PriceModifierInterface;
use Magento\CatalogRule\Model\RuleFactory;

class PriceModifier implements PriceModifierInterface
{
    /**
     * @var \Magento\CatalogRule\Model\RuleFactory
     */
    protected $ruleFactory;

    /**
     * @param RuleFactory $ruleFactory
     */
    public function __construct(RuleFactory $ruleFactory)
    {
        $this->ruleFactory = $ruleFactory;
    }

    /**
     * Modify price
     *
     * @param mixed $price
     * @param Product $product
     * @return mixed
     */
    public function modifyPrice($price, Product $product)
    {
        if ($price !== null) {
            $resultPrice = $this->ruleFactory->create()->calcProductPriceRule($product, $price);
            if ($resultPrice !== null) {
                $price = $resultPrice;
            }
        }
        return $price;
    }
}
