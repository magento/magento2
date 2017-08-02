<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway;

/**
 * Class ConfigInterfaceFactory
 * @package Magento\Payment\Gateway
 * @api
 * @since 2.1.0
 */
interface ConfigFactoryInterface
{
    /**
     * @param string|null $paymentCode
     * @param string|null $pathPattern
     * @return mixed
     * @since 2.1.0
     */
    public function create($paymentCode = null, $pathPattern = null);
}
