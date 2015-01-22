<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Order;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Items block on order's view page
 */
class Items extends Block
{
    /**
     * Grand total css selector
     *
     * @var string
     */
    protected $grandTotal = '.grand_total span.price';

    /**
     * Item selector.
     *
     * @var string
     */
    protected $itemSelector = './/tbody[tr[td[contains(., "%s")]]]';

    /**
     * Check if item is visible in print order page.
     *
     * @param \Magento\Mtf\Fixture\InjectableFixture $product
     * @return bool
     */
    public function isItemVisible($product)
    {
        return $this->_rootElement->find(
            sprintf($this->itemSelector, $product->getName()),
            Locator::SELECTOR_XPATH
        )->isVisible();
    }

    /**
     * Get grand total price
     *
     * @return string|null
     */
    public function getGrandTotal()
    {
        return $this->escapeCurrency($this->_rootElement->find($this->grandTotal)->getText());
    }

    /**
     * Method that escapes currency symbols
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
