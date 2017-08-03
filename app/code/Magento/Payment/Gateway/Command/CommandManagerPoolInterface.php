<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway\Command;

use Magento\Framework\Exception\NotFoundException;

/**
 * Interface CommandManagerPoolInterface
 * @package Magento\Payment\Gateway\Command
 * @api
 * @since 2.1.0
 */
interface CommandManagerPoolInterface
{
    /**
     * Returns Command executor for defined payment provider
     *
     * @param string $paymentProviderCode
     * @return CommandManagerInterface
     * @throws NotFoundException
     * @since 2.1.0
     */
    public function get($paymentProviderCode);
}
