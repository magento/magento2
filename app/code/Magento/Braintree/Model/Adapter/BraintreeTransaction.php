<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Model\Adapter;

use \Braintree_Transaction;
use \Braintree_Result_Error;
use \Braintree_Result_Successful;

/**
 * BraintreeTransaction
 *
 * @codeCoverageIgnore
 */
class BraintreeTransaction
{
    /**
     * @param array $attribs
     * @return \Braintree_Result_Successful|\Braintree_Result_Error
     */
    public function sale(array $attribs)
    {
        return \Braintree_Transaction::sale($attribs);
    }

    /**
     * @param string $transactionId
     * @param null|float $amount
     * @return \Braintree_Result_Successful|\Braintree_Result_Error
     */
    public function submitForSettlement($transactionId, $amount = null)
    {
        return \Braintree_Transaction::submitForSettlement($transactionId, $amount);
    }

    /**
     * @param string $id
     * @return \Braintree_Transaction
     */
    public function find($id)
    {
        return \Braintree_Transaction::find($id);
    }

    /**
     * @param string $transactionId
     * @return \Braintree_Result_Successful|\Braintree_Result_Error
     */
    public function void($transactionId)
    {
        return \Braintree_Transaction::void($transactionId);
    }

    /**
     * @param string $transactionId
     * @param null|float $amount
     * @return \Braintree_Result_Successful|\Braintree_Result_Error
     */
    public function refund($transactionId, $amount = null)
    {
        return \Braintree_Transaction::refund($transactionId, $amount);
    }

    /**
     * @param string $transactionId
     * @param array $attribs
     * @return \Braintree_Result_Successful|\Braintree_Result_Error
     */
    public function cloneTransaction($transactionId, array $attribs)
    {
        return \Braintree_Transaction::cloneTransaction($transactionId, $attribs);
    }
}
