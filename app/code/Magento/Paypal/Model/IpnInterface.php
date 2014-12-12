<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Paypal\Model;

interface IpnInterface
{
    /**
     * Get ipn data, send verification to PayPal, run corresponding handler
     *
     * @return void
     * @throws \Exception
     */
    public function processIpnRequest();
}
