<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway\Command;

use Magento\Framework\Exception\NotFoundException;
use Magento\Payment\Gateway\Command;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactoryInterface;
use Magento\Payment\Model\InfoInterface;

/**
 * Class CommandManager
 * @package Magento\Payment\Gateway\Command
 * @api
 * @since 100.1.0
 */
class CommandManager implements CommandManagerInterface
{
    /**
     * @var CommandPoolInterface
     */
    private $commandPool;

    /**
     * @var PaymentDataObjectFactoryInterface
     */
    private $paymentDataObjectFactory;

    /**
     * CommandExecutor constructor.
     * @param CommandPoolInterface $commandPool
     * @param PaymentDataObjectFactoryInterface $paymentDataObjectFactory
     * @since 100.1.0
     */
    public function __construct(
        CommandPoolInterface $commandPool,
        PaymentDataObjectFactoryInterface $paymentDataObjectFactory
    ) {
        $this->commandPool = $commandPool;
        $this->paymentDataObjectFactory = $paymentDataObjectFactory;
    }

    /**
     * Executes command by code
     *
     * @param string $commandCode
     * @param InfoInterface|null $payment
     * @param array $arguments
     * @return ResultInterface|null
     * @throws NotFoundException
     * @throws CommandException
     * @since 100.1.0
     */
    public function executeByCode($commandCode, InfoInterface $payment = null, array $arguments = [])
    {
        $commandSubject = $arguments;
        if ($payment !== null) {
            $commandSubject['payment'] = $this->paymentDataObjectFactory->create($payment);
        }

        return $this->commandPool
            ->get($commandCode)
            ->execute($commandSubject);
    }

    /**
     * Executes command
     *
     * @param CommandInterface $command
     * @param InfoInterface|null $payment
     * @param array $arguments
     * @return ResultInterface|null
     * @throws CommandException
     * @since 100.1.0
     */
    public function execute(CommandInterface $command, InfoInterface $payment = null, array $arguments = [])
    {
        $commandSubject = $arguments;
        if ($payment !== null) {
            $commandSubject['payment'] = $this->paymentDataObjectFactory->create($payment);
        }

        return $command->execute($commandSubject);
    }

    /**
     * Retrieves operation
     *
     * @param string $commandCode
     * @return CommandInterface
     * @throws NotFoundException
     * @since 100.1.0
     */
    public function get($commandCode)
    {
        return $this->commandPool->get($commandCode);
    }
}
