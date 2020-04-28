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
 * Chooses the best method of accepting the payment based on the status of the transaction
 *
 * @deprecated 100.3.3 Starting from Magento 2.3.4 Authorize.net payment method core integration is deprecated in favor of
 * official payment integration available on the marketplace
 */
class AcceptPaymentStrategyCommand implements CommandInterface
{
    private const ACCEPT_FDS = 'accept_fds';
    private const NEEDS_APPROVAL_STATUSES = [
        'FDSPendingReview',
        'FDSAuthorizedPendingReview'
    ];

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
        if ($this->shouldAcceptInGateway($commandSubject)) {
            $this->commandPool->get(self::ACCEPT_FDS)
                ->execute($commandSubject);
        }
    }

    /**
     * Determines if the transaction needs to be accepted in the gateway
     *
     * @param array $commandSubject
     * @return bool
     * @throws CommandException
     */
    private function shouldAcceptInGateway(array $commandSubject): bool
    {
        $details = $this->commandPool->get('get_transaction_details')
            ->execute($commandSubject)
            ->get();

        return in_array($details['transaction']['transactionStatus'], self::NEEDS_APPROVAL_STATUSES);
    }
}
