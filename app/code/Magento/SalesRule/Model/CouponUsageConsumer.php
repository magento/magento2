<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Model;

use Magento\SalesRule\Model\Coupon\Usage\UpdateInfoFactory;
use Magento\SalesRule\Model\Coupon\Usage\Processor as CouponUsageProcessor;
use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Exception\NotFoundException;
use Psr\Log\LoggerInterface;

/**
 * Consumer for coupon usage update
 */
class CouponUsageConsumer
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CouponUsageProcessor
     */
    private $processor;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var UpdateInfoFactory
     */
    private $updateInfoFactory;

    /**
     * @param UpdateInfoFactory $updateInfoFactory
     * @param CouponUsageProcessor $processor
     * @param LoggerInterface $logger
     * @param SerializerInterface $serializer
     * @param EntityManager $entityManager
     */
    public function __construct(
        UpdateInfoFactory $updateInfoFactory,
        CouponUsageProcessor $processor,
        LoggerInterface $logger,
        SerializerInterface $serializer,
        EntityManager $entityManager
    ) {
        $this->updateInfoFactory = $updateInfoFactory;
        $this->processor = $processor;
        $this->logger = $logger;
        $this->serializer = $serializer;
        $this->entityManager = $entityManager;
    }

    /**
     * Process coupon usage update
     *
     * @param OperationInterface $operation
     * @return void
     * @throws \Exception
     */
    public function process(OperationInterface $operation): void
    {
        try {
            $serializedData = $operation->getSerializedData();
            $data = $this->serializer->unserialize($serializedData);
            $updateInfo = $this->updateInfoFactory->create();
            $updateInfo->setData($data);
            $this->processor->updateCouponUsages($updateInfo);
            $this->processor->updateRuleUsages($updateInfo);
        } catch (NotFoundException $e) {
            $this->logger->critical($e->getMessage());
            $status = OperationInterface::STATUS_TYPE_NOT_RETRIABLY_FAILED;
            $errorCode = $e->getCode();
            $message = $e->getMessage();
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            $status = OperationInterface::STATUS_TYPE_NOT_RETRIABLY_FAILED;
            $errorCode = $e->getCode();
            $message = __('Sorry, something went wrong during rule usage update. Please see log for details.');
        }

        $operation->setStatus($status ?? OperationInterface::STATUS_TYPE_COMPLETE)
            ->setErrorCode($errorCode ?? null)
            ->setResultMessage($message ?? null);

        $this->entityManager->save($operation);
    }
}
