<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Gateway\Command;

use Magento\AuthorizenetAcceptjs\Gateway\SubjectReader;
use Magento\Payment\Gateway\Command\CommandException;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\CommandInterface;

/**
 * Chooses the best method of returning the payment based on the status of the transaction
 *
 * @deprecated 100.3.3 Starting from Magento 2.3.4 Authorize.net payment method core integration is deprecated in favor of
 * official payment integration available on the marketplace
 */
class RefundTransactionStrategyCommand implements CommandInterface
{
    private const REFUND = 'refund_settled';
    private const VOID = 'void';

    /**
     * @var CommandPoolInterface
     */
    private $commandPool;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @param CommandPoolInterface $commandPool
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        CommandPoolInterface $commandPool,
        SubjectReader $subjectReader
    ) {
        $this->commandPool = $commandPool;
        $this->subjectReader = $subjectReader;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $commandSubject): void
    {
        $command = $this->getCommand($commandSubject);

        $this->commandPool->get($command)
            ->execute($commandSubject);
    }

    /**
     * Determines the command that should be used based on the status of the transaction
     *
     * @param array $commandSubject
     * @return string
     * @throws CommandException
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    private function getCommand(array $commandSubject): string
    {
        $details = $this->commandPool->get('get_transaction_details')
            ->execute($commandSubject)
            ->get();

        if ($this->canVoid($details, $commandSubject)) {
            return self::VOID;
        }

        if ($details['transaction']['transactionStatus'] !== 'settledSuccessfully') {
            throw new CommandException(__('This transaction cannot be refunded with its current status.'));
        }

        return self::REFUND;
    }

    /**
     * Checks if void command can be performed.
     *
     * @param array $details
     * @param array $commandSubject
     * @return bool
     * @throws CommandException
     */
    private function canVoid(array $details, array $commandSubject) :bool
    {
        if ($details['transaction']['transactionStatus'] === 'capturedPendingSettlement') {
            if ((float) $details['transaction']['authAmount'] !== (float) $commandSubject['amount']) {
                throw new CommandException(
                    __('The transaction has not been settled, a partial refund is not yet available.')
                );
            }

            return true;
        }

        return false;
    }
}
