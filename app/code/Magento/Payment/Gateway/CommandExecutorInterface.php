<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway;

/**
 * Interface CommandExecutorInterface
 * @api
 */
interface CommandExecutorInterface
{
    /**
     * Performs command
     *
     * @param string $commandCode
     * @param array $arguments
     * @return null|Command\ResultInterface
     */
    public function executeCommand($commandCode, array $arguments = []);
}
