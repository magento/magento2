<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Block\Onepage\Payment;

use Magento\Mtf\Block\Block;
use Magento\Payment\Test\Fixture\CreditCard;

/**
 * Checkout payment method block.
 */
class Method extends Block
{
    /**
     * Wait element
     *
     * @var string
     */
    protected $waitElement = '.loading-mask';

    /**
     * Purchase order number selector
     *
     * @var string
     */
    protected $purchaseOrderNumber = '#po_number';

    /**
     * Continue checkout button.
     *
     * @var string
     */
    protected $placeOrderButton = '.actions-toolbar .checkout';

    /**
     * Billing address block selector.
     *
     * @var string
     */
    protected $billingAddressSelector = '.payment-method-billing-address';

    /**
     * Place order.
     *
     * @return void
     */
    public function clickPlaceOrder()
    {
        $this->_rootElement->find($this->placeOrderButton)->click();
        $this->waitForElementNotVisible($this->waitElement);
    }

    /**
     * Get "Billing Address" block.
     *
     * @return \Magento\Checkout\Test\Block\Onepage\Payment\Methods\Billing
     */
    public function getBillingBlock()
    {
        $element = $this->_rootElement->find($this->billingAddressSelector);

        return $this->blockFactory->create(
            '\Magento\Checkout\Test\Block\Onepage\Payment\Methods\Billing',
            ['element' => $element]
        );
    }
}
