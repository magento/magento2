<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order\Create\Billing;

use Magento\Mtf\Block\Block;

/**
 * Adminhtml sales order create payment method block.
 */
class Method extends Block
{
    /**
     * Payment method.
     *
     * @var string
     */
    protected $paymentMethod = '#p_method_%s';

    /**
     * Purchase order number selector.
     *
     * @var string
     */
    protected $purchaseOrderNumber = '#po_number';

    /**
     * Magento loader selctor.
     *
     * @var string
     */
    protected $loader = '[data-role=loader]';

    /**
     * Select payment method.
     *
     * @param array $paymentCode
     * @return void
     */
    public function selectPaymentMethod(array $paymentCode)
    {
        $paymentInput = $this->_rootElement->find(sprintf($this->paymentMethod, $paymentCode['method']));
        if ($paymentInput->isVisible()) {
            $paymentInput->click();
            $this->waitForElementNotVisible($this->loader);
        }
        if (isset($paymentCode['po_number']) && $paymentCode['po_number'] !== "-") {
            $this->_rootElement->find($this->purchaseOrderNumber)->setValue($paymentCode['po_number']);
        }
    }
}
