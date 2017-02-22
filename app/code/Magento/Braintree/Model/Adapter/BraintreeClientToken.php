<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Model\Adapter;

use \Braintree_ClientToken;
use \Braintree_Result_Error;
use \Braintree_Result_Successful;

/**
 * BraintreeClientToken
 *
 * @codeCoverageIgnore
 */
class BraintreeClientToken
{
    /**
     * @param array $params
     * @return \Braintree_Result_Successful|\Braintree_Result_Error|null
     */
    public function generate(array $params = [])
    {
        try {
            return \Braintree_ClientToken::generate($params);
        } catch (\Exception $e) {
            return null;
        }
    }
}
