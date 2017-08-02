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
 * @since 2.0.0
 */
interface CommandInterface
{
    /**
     * Executes command basing on business object
     *
     * @param array $commandSubject
     * @return null|Command\ResultInterface
     * @throws CommandException
     * @since 2.0.0
     */
    public function execute(array $commandSubject);
}
