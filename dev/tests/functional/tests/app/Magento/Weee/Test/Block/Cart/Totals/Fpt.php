<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Weee\Test\Block\Cart\Totals;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Cart totals fpt block
 */
class Fpt extends Block
{
    /**
     * FPT totals locator
     *
     * @var string
     */
    protected $totalFpt = '.price';

    /**
     * Get FPT Total Text
     *
     * @return string
     */
    public function getTotalFpt()
    {
        $grandTotal = $this->_rootElement->find($this->totalFpt, Locator::SELECTOR_CSS)->getText();
        return $this->escapeCurrency($grandTotal);
    }

    /**
     * Escape currency in price
     *
     * @param string $price
     * @return string|null
     */
    protected function escapeCurrency($price)
    {
        preg_match("/^\\D*\\s*([\\d,\\.]+)\\s*\\D*$/", $price, $matches);
        return (isset($matches[1])) ? $matches[1] : null;
    }
}
