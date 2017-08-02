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

/**
 * Class \Magento\CatalogRule\Model\Product\PriceModifier
 *
 * @since 2.0.0
 */
class PriceModifier implements PriceModifierInterface
{
    /**
     * @var \Magento\CatalogRule\Model\RuleFactory
     * @since 2.0.0
     */
    protected $ruleFactory;

    /**
     * @param RuleFactory $ruleFactory
     * @since 2.0.0
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
     * @since 2.0.0
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
