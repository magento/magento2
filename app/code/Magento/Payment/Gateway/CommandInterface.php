<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway;

/**
 * Interface CommandInterface
 * @package Magento\Payment\Gateway
 * @api
 */
interface CommandInterface
{
    /**
     * Executes command basing on business object
     *
     * @param array $commandSubject
     * @return null|Command\ResultInterface
     */
    public function execute(array $commandSubject);
}
