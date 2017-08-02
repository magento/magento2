<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Invoice\Total;

/**
 * Class \Magento\Sales\Model\Order\Invoice\Total\Grand
 *
 * @since 2.0.0
 */
class Grand extends AbstractTotal
{
    /**
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @return $this
     * @since 2.0.0
     */
    public function collect(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        /**
         * Check order grand total and invoice amounts
         */
        if ($invoice->isLast()) {
            //
        }
        return $this;
    }
}
