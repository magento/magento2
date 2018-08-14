<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestModuleFakePaymentMethod\Gateway\Command;

use Magento\Payment\Gateway\CommandInterface;

class DoNothingCommand implements CommandInterface
{
    /**
     * @inheritDoc
     */
    public function execute(array $commandSubject)
    {
        // This is fake. No action expected.
    }
}
