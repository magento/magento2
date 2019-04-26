<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLegacySynchronization\Model;

use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Consumer class for asynchronous legacy synchronization.
 * Works bot from MSI to legacy and vice versa.
 */
class AsyncConsumer
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var SynchronizeInventoryData
     */
    private $synchronizeInventoryData;

    /**
     * @param SerializerInterface $serializer
     * @param SynchronizeInventoryData $synchronizeInventoryData
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        SerializerInterface $serializer,
        SynchronizeInventoryData $synchronizeInventoryData
    ) {
        $this->serializer = $serializer;
        $this->synchronizeInventoryData = $synchronizeInventoryData;
    }

    /**
     * Processing batch operations for legacy stock synchronization
     *
     * @param OperationInterface $operation
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function processOperations(OperationInterface $operation): void
    {
        $data = $this->serializer->unserialize($operation->getSerializedData());
        $this->synchronizeInventoryData->execute($data['destination'], $data['items']);
    }
}
