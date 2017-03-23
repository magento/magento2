<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway\Command;

use Magento\Framework\Exception\NotFoundException;

/**
 * Interface CommandManagerPoolInterface
 * @package Magento\Payment\Gateway\Command
 * @api
 */
interface CommandManagerPoolInterface
{
    /**
     * Returns Command executor for defined payment provider
     *
     * @param string $paymentProviderCode
     * @return CommandManagerInterface
     * @throws NotFoundException
     */
    public function get($paymentProviderCode);
}
