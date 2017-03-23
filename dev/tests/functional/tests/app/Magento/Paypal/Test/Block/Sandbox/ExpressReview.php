<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Test\Block\Sandbox;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Review order on PayPal side and continue.
 */
class ExpressReview extends Block
{
    /**
     * Continue button on order review page on PayPal side.
     *
     * @var string
     */
    protected $continue = '#confirmButtonTop';

    /**
     * Total search mask.
     *
     * @var string
     */
    protected $total = '#transactionCart .ng-binding';

    /**
     * Method that escapes currency symbols.
     *
     * @param string $price
     * @return string|null
     */
    protected function escapeCurrency($price)
    {
        return preg_replace("/[^0-9\.,]/", '', $price);
    }

    /**
     * Get Total text.
     *
     * @return array|string
     */
    public function getTotal()
    {
        $this->waitForElementVisible($this->total);
        $total = $this->_rootElement->find($this->total, Locator::SELECTOR_CSS)->getText();
        return $this->escapeCurrency($total);
    }

    /**
     * Review order on PayPal side and continue.
     *
     * @return void
     */
    public function reviewAndContinue()
    {
        $this->waitForElementVisible($this->continue);
        $this->_rootElement->find($this->continue)->click();
    }
}
