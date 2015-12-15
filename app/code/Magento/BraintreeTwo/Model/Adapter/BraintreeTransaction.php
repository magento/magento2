<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Model\Adapter;

use Braintree\Transaction;

/**
 * BraintreeTransaction
 *
 * @codeCoverageIgnore
 */
class BraintreeTransaction
{
    /**
     * @param array $attributes
     * @return \Braintree\Result\Successful|\Braintree\Result\Error
     */
    public function sale(array $attributes)
    {
        return Transaction::sale($attributes);
    }

    /**
     * @param string $transactionId
     * @param null|float $amount
     * @return \Braintree\Result\Successful|\Braintree\Result\Error
     */
    public function submitForSettlement($transactionId, $amount = null)
    {
        return Transaction::submitForSettlement($transactionId, $amount);
    }

    /**
     * @param string $transactionId
     * @return \Braintree\Result\Successful|\Braintree\Result\Error
     */
    public function void($transactionId)
    {
        return Transaction::void($transactionId);
    }

    /**
     * @param string $transactionId
     * @param null|float $amount
     * @return \Braintree\Result\Successful|\Braintree\Result\Error
     */
    public function refund($transactionId, $amount = null)
    {
        return Transaction::refund($transactionId, $amount);
    }

    /**
     * Clone original transaction
     * @param string $transactionId
     * @param array $attributes
     * @return mixed
     */
    public function cloneTransaction($transactionId, array $attributes)
    {
        return Transaction::cloneTransaction($transactionId, $attributes);
    }
}
