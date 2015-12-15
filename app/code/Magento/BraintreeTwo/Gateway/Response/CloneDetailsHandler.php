<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Gateway\Response;

use Braintree\Transaction;

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
        $transaction = $this->subjectReader->readTransaction($response);

        $this->payment->setTransactionId($transaction->id);
    }
}
