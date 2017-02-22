<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Model\Adapter;

use \Braintree_CreditCard;
use \Braintree_Result_Error;
use \Braintree_Result_Successful;

/**
 * BraintreeCreditCard
 *
 * @codeCoverageIgnore
 */
class BraintreeCreditCard
{
    /**
     * @param string $id
     * @return \Braintree_CreditCard
     */
    public function find($token)
    {
        return \Braintree_CreditCard::find($token);
    }

    /**
     * @param string $token
     * @return \Braintree_Result_Successful
     */
    public function delete($token)
    {
        return \Braintree_CreditCard::delete($token);
    }
}
