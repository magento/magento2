<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Model\Adapter;

use Braintree\Configuration;

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
        return Configuration::environment($value);
    }

    /**
     * @param string|null $value
     * @return mixed
     */
    public function merchantId($value = null)
    {
        return Configuration::merchantId($value);
    }

    /**
     * @param string|null $value
     * @return mixed
     */
    public function publicKey($value = null)
    {
        return Configuration::publicKey($value);
    }

    /**
     * @param string|null $value
     * @return mixed
     */
    public function privateKey($value = null)
    {
        return Configuration::privateKey($value);
    }
}
