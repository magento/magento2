<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway;

use Magento\Payment\Gateway\Command\CommandException;

/**
 * Interface CommandInterface
 * @package Magento\Payment\Gateway
 * @api
 * @since 100.0.2
 */
interface CommandInterface
{
    /**
     * Executes command basing on business object
     *
     * @param array $commandSubject
     * @return null|Command\ResultInterface
     * @throws CommandException
     */
    public function execute(array $commandSubject);
}
