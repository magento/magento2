<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Gateway\Command;

use Magento\Payment\Gateway\Command;
use Magento\Payment\Gateway\Command\CommandException;
use Magento\Payment\Gateway\Command\CommandManagerInterface;
use Magento\Payment\Gateway\CommandInterface;

class RefundMacroCommand implements CommandInterface
{
    /**
     * @var CommandManagerInterface
     */
    private $commandManager;

    /**
     * RefundMacroCommand constructor.
     * @param CommandManagerInterface $commandManager
     */
    public function __construct(
        CommandManagerInterface $commandManager
    ) {
        $this->commandManager = $commandManager;
    }

    /**
     * Performs refund for invoice.
     * If refund fails, we are alerted that transaction is not settled and can't be refunded
     * It means that this transaction should be voided
     *
     * @param array $commandSubject
     * @return null|Command\ResultInterface
     * @throws CommandException
     */
    public function execute(array $commandSubject)
    {
        try {
            $this->commandManager->executeByCode('refund_simple', null, $commandSubject);
        } catch (Command\CommandException $e) {
            $this->commandManager->executeByCode('void', null, $commandSubject);
        }
    }
}
