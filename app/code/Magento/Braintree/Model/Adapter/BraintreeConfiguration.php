<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Model\Adapter;

use \Braintree_Configuration;

/**
 * BraintreeConfiguration
 *
 * @codeCoverageIgnore
 */
class BraintreeConfiguration
{
    /**
     * @param string|null $value
     * @return mixed
     */
    public function environment($value = null)
    {
        return \Braintree_Configuration::environment($value);
    }

    /**
     * @param string|null $value
     * @return mixed
     */
    public function merchantId($value = null)
    {
        return \Braintree_Configuration::merchantId($value);
    }

    /**
     * @param string|null $value
     * @return mixed
     */
    public function publicKey($value = null)
    {
        return \Braintree_Configuration::publicKey($value);
    }

    /**
     * @param string|null $value
     * @return mixed
     */
    public function privateKey($value = null)
    {
        return \Braintree_Configuration::privateKey($value);
    }
}
