<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ProductAlert\Model\Mailing;

use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Serialize\Serializer\Json;
use Psr\Log\LoggerInterface;

/**
 * Class for processing Product Alerts from the messages queue
 */
class Consumer
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var AlertProcessor
     */
    private $alertProcessor;

    /**
     * @param LoggerInterface $logger
     * @param Json $jsonSerializer
     * @param EntityManager $entityManager
     * @param AlertProcessor $alertProcessor
     */
    public function __construct(
        LoggerInterface $logger,
        Json $jsonSerializer,
        EntityManager $entityManager,
        AlertProcessor $alertProcessor
    ) {
        $this->logger = $logger;
        $this->jsonSerializer = $jsonSerializer;
        $this->entityManager = $entityManager;
        $this->alertProcessor = $alertProcessor;
    }

    /**
     * Processing Product Alerts
     *
     * @param OperationInterface $operation
     */
    public function process(OperationInterface $operation): void
    {
        $status = OperationInterface::STATUS_TYPE_COMPLETE;
        $message = __('Product alerts are sent successfully.');
        $errorCode = null;

        try {
            $data = $this->jsonSerializer->unserialize($operation->getSerializedData());
            $this->alertProcessor->process($data['alert_type'], $data['customer_ids'], (int)$data['website_id']);
        } catch (\Throwable $e) {
            $this->logger->critical($e->getMessage());
            $status = OperationInterface::STATUS_TYPE_NOT_RETRIABLY_FAILED;
            $errorCode = $e->getCode();
            $message = __('Sorry, something went wrong during mailing product alerts. Please see log for details.');
        }

        $operation->setStatus($status)
            ->setErrorCode($errorCode)
            ->setResultMessage($message);
        $this->entityManager->save($operation);
    }
}
