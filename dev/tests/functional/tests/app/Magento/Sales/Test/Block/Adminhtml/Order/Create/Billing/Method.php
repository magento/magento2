<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order\Create\Billing;

use Mtf\Block\Block;

/**
 * Class Method
 * Adminhtml sales order create payment method block
 */
class Method extends Block
{
    /**
     * Payment method
     *
     * @var string
     */
    protected $paymentMethod = '#p_method_%s';

    /**
     * Purchase order number selector
     *
     * @var string
     */
    protected $purchaseOrderNumber = '#po_number';

    /**
     * Select payment method
     *
     * @param array $paymentCode
     */
    public function selectPaymentMethod(array $paymentCode)
    {
        // Click on rootElement to solve overlapping inner elements by header menu.
        $this->_rootElement->click();
        $paymentInput = $this->_rootElement->find(sprintf($this->paymentMethod, $paymentCode['method']));
        if ($paymentInput->isVisible()) {
            $paymentInput->click();
        }
        if (isset($paymentCode['po_number']) && $paymentCode['po_number'] !== "-") {
            $this->_rootElement->find($this->purchaseOrderNumber)->setValue($paymentCode['po_number']);
        }
    }
}
