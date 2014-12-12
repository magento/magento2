<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
