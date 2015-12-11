<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway;

/**
 * Class ConfigInterfaceFactory
 * @package Magento\Payment\Gateway
 * @api
 */
interface ConfigFactoryInterface
{
    public function create($paymentCode = null, $pathPattern = null);
}
