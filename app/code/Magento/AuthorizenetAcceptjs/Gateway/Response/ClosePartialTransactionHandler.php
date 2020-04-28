<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Gateway\Response;

use Magento\Sales\Model\Order\Payment;

/**
 * Determines that parent transaction should be close for partial refund operation.
 *
 * @deprecated 100.3.3 Starting from Magento 2.3.4 Authorize.net payment method core integration is deprecated in favor of
 * official payment integration available on the marketplace
 */
class ClosePartialTransactionHandler extends CloseTransactionHandler
{
    /**
     * Whether parent transaction should be closed.
     *
     * @param Payment $payment
     * @return bool
     */
    public function shouldCloseParentTransaction(Payment $payment)
    {
        return !(bool)$payment->getCreditmemo()->getInvoice()->canRefund();
    }
}
