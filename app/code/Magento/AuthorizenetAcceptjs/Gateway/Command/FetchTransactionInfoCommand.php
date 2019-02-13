<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Gateway\Command;

use Magento\AuthorizenetAcceptjs\Gateway\Config;
use Magento\AuthorizenetAcceptjs\Gateway\SubjectReader;
use Magento\Payment\Gateway\Command\CommandPool;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;

/**
 * Syncs the transaction status with authorize.net
 */
class FetchTransactionInfoCommand implements CommandInterface
{
    /**
     * @var CommandPool
     */
    private $commandPool;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var HandlerInterface|null
     */
    private $handler;

    /**
     * @param CommandPoolInterface $commandPool
     * @param SubjectReader $subjectReader
     * @param Config $config
     * @param HandlerInterface|null $handler
     */
    public function __construct(
        CommandPoolInterface $commandPool,
        SubjectReader $subjectReader,
        Config $config,
        HandlerInterface $handler = null
    ) {
        $this->commandPool = $commandPool;
        $this->subjectReader = $subjectReader;
        $this->config = $config;
        $this->handler = $handler;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $commandSubject): array
    {
        $paymentDO = $this->subjectReader->readPayment($commandSubject);
        $order = $paymentDO->getOrder();

        $command = $this->commandPool->get('get_transaction_details');
        $result = $command->execute($commandSubject);
        $response = $result->get();

        if ($this->handler) {
            $this->handler->handle($commandSubject, $response);
        }

        $additionalInformationKeys = $this->config->getTransactionInfoSyncKeys($order->getStoreId());
        $rawDetails = [];
        foreach ($additionalInformationKeys as $key) {
            if (isset($response['transaction'][$key])) {
                $rawDetails[$key] = $response['transaction'][$key];
            }
        }

        return $rawDetails;
    }
}
