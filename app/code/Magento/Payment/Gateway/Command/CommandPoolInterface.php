<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Payment\Gateway\Command;

use Magento\Framework\Exception\NotFoundException;
use Magento\Payment\Gateway\CommandInterface;

/**
 * Interface CommandPoolInterface
 * @package Magento\Payment\Gateway\Command
 * @api
 * @since 100.0.2
 */
interface CommandPoolInterface
{
    /**
     * Retrieves operation
     *
     * @param string $commandCode
     * @return CommandInterface
     * @throws NotFoundException
     */
    public function get($commandCode);
}
