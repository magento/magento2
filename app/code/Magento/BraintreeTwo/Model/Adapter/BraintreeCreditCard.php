<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Model\Adapter;

use Braintree\CreditCard;
use Braintree\Result\Error;
use Braintree\Result\Successful;

/**
 * BraintreeCreditCard
 *
 * @codeCoverageIgnore
 */
class BraintreeCreditCard
{
    /**
     * @param string $id
     * @return \Braintree\CreditCard
     */
    public function find($token)
    {
        return CreditCard::find($token);
    }

    /**
     * @param string $token
     * @return \Braintree\Result\Successful
     */
    public function delete($token)
    {
        return CreditCard::delete($token);
    }
}
