<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model;

/**
 * Interface \Magento\Paypal\Model\IpnInterface
 *
 * @since 2.0.0
 */
interface IpnInterface
{
    /**
     * Get ipn data, send verification to PayPal, run corresponding handler
     *
     * @return void
     * @throws \Exception
     * @since 2.0.0
     */
    public function processIpnRequest();
}
