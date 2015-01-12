<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Block\Cart\Sidebar;

use Magento\Checkout\Block\Cart\AbstractCart;

/**
 * Sidebar totals block
 */
class Totals extends AbstractCart
{
    /**
     * Get shopping cart subtotal.
     *
      * @return  float
     */
    public function getSubtotal()
    {
        $subtotal = 0;
        $totals = $this->getTotals();
        if (isset($totals['subtotal'])) {
            $subtotal = $totals['subtotal']->getValue();
        }
        return $subtotal;
    }
}
