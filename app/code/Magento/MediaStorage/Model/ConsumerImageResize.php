<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaStorage\Model;

use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Psr\Log\LoggerInterface;
use Magento\MediaStorage\Service\ImageResize;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Exception\NotFoundException;

/**
 * Consumer for image resize
 */
class ConsumerImageResize
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
     * @var ImageResize
     */
    private $resize;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @param ImageResize $resize
     * @param LoggerInterface $logger
     * @param SerializerInterface $serializer
     * @param EntityManager $entityManager
     */
    public function __construct(
        ImageResize $resize,
        LoggerInterface $logger,
        SerializerInterface $serializer,
        EntityManager $entityManager
    ) {
        $this->resize = $resize;
        $this->logger = $logger;
        $this->serializer = $serializer;
        $this->entityManager = $entityManager;
    }

    /**
     * Image resize
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
            $this->resize->resizeFromImageName($data['filename']);
        } catch (NotFoundException $e) {
            $this->logger->critical($e->getMessage());
            $status = OperationInterface::STATUS_TYPE_NOT_RETRIABLY_FAILED;
            $errorCode = $e->getCode();
            $message = $e->getMessage();
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            $status = OperationInterface::STATUS_TYPE_NOT_RETRIABLY_FAILED;
            $errorCode = $e->getCode();
            $message = __('Sorry, something went wrong during image resize. Please see log for details.');
        }

        $operation->setStatus($status ?? OperationInterface::STATUS_TYPE_COMPLETE)
            ->setErrorCode($errorCode ?? null)
            ->setResultMessage($message ?? null);

        $this->entityManager->save($operation);
    }
}
