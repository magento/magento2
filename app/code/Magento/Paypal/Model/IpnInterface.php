<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model;

/**
 * Interface \Magento\Paypal\Model\IpnInterface
 *
 */
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
