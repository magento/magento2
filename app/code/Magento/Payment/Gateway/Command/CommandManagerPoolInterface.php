<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Payment\Gateway\Command;

use Magento\Framework\Exception\NotFoundException;

/**
 * Interface CommandManagerPoolInterface
 * @package Magento\Payment\Gateway\Command
 * @api
 * @since 100.1.0
 */
interface CommandManagerPoolInterface
{
    /**
     * Returns Command executor for defined payment provider
     *
     * @param string $paymentProviderCode
     * @return CommandManagerInterface
     * @throws NotFoundException
     * @since 100.1.0
     */
    public function get($paymentProviderCode);
}
