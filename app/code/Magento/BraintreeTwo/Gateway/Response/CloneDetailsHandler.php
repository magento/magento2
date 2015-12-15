<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Gateway\Response;

use Braintree\Transaction;
use Magento\Sales\Model\Order\Payment;

/**
 * Class CloneDetailsHandler
 */
class CloneDetailsHandler extends CaptureDetailsHandler
{

    /**
     * @inheritdoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        parent::handle($handlingSubject, $response);

        /** @var \Braintree\Transaction $transaction */
        $transaction = $response['object']->transaction;

        $this->payment->setTransactionId($transaction->id);
    }
}