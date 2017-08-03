<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway\Command;

use Magento\Payment\Gateway\Command;
use Magento\Payment\Gateway\CommandInterface;

/**
 * Null command. Does nothing.
 *
 * Use this class as an implementation of CommandInterface to ignore some action
 * or in case if command invoked by Magento has no sense for payment method.
 *
 * @api
 * @since 2.0.0
 */
class NullCommand implements CommandInterface
{
    /**
     * Process command without any side effect.
     *
     * @param array $commandSubject
     *
     * @return null|Command\ResultInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function execute(array $commandSubject)
    {
        return null;
    }
}
