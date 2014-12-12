<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Weee\Test\Block\Cart;

use Mtf\Client\Element\Locator;

/**
 * Cart totals fpt block
 */
class Totals extends \Magento\Checkout\Test\Block\Cart\Totals
{
    /**
     * Fpt block selector
     *
     * @var string
     */
    protected $fptBlock = './/tr[normalize-space(td)="FPT"]';

    /**
     * Get block fpt totals
     *
     * @return \Magento\Weee\Test\Block\Cart\Totals\Fpt
     */
    public function getFptBlock()
    {
        return $this->blockFactory->create(
            'Magento\Weee\Test\Block\Cart\Totals\Fpt',
            ['element' => $this->_rootElement->find($this->fptBlock, Locator::SELECTOR_XPATH)]
        );
    }
}
