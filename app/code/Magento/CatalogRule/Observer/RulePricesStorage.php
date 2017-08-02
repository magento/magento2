<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Observer;

/**
 * Class \Magento\CatalogRule\Observer\RulePricesStorage
 *
 * @since 2.0.0
 */
class RulePricesStorage
{
    /**
     * Store calculated catalog rules prices for products
     * Prices collected per website, customer group, date and product
     *
     * @var array
     * @since 2.0.0
     */
    private $rulePrices = [];

    /**
     * @param string $id
     * @return false|float
     * @since 2.0.0
     */
    public function getRulePrice($id)
    {
        return isset($this->rulePrices[$id]) ? $this->rulePrices[$id] : false;
    }

    /**
     * @param string $id
     * @return bool
     * @since 2.0.0
     */
    public function hasRulePrice($id)
    {
        return isset($this->rulePrices[$id]);
    }

    /**
     * @param string $id
     * @param float $price
     * @return void
     * @since 2.0.0
     */
    public function setRulePrice($id, $price)
    {
        $this->rulePrices[$id] = $price;
    }
}
