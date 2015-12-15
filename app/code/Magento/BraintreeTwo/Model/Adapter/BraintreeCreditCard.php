<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Model\Adapter;

use Braintree\CreditCard;

/**
 * BraintreeCreditCard
 *
 * @codeCoverageIgnore
 */
class BraintreeCreditCard
{
    /**
     * @param string $id
     * @return \Braintree\CreditCard|null
     */
    public function find($token)
    {
        try {
            return CreditCard::find($token);
        } catch (\Exception $e) {
            return null;
        }
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
