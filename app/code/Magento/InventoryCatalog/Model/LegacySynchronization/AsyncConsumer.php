<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model\LegacySynchronization;

use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\Framework\Serialize\SerializerInterface;

class AsyncConsumer
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var SetDataToDestination
     */
    private $setDataToDestination;

    /**
     * Consumer constructor.
     * @param SerializerInterface $serializer
     * @param SetDataToDestination $setDataToDestination
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        SerializerInterface $serializer,
        SetDataToDestination $setDataToDestination
    ) {
        $this->serializer = $serializer;
        $this->setDataToDestination = $setDataToDestination;
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
        $this->setDataToDestination->execute($data['direction'], $data['skus']);
    }
}
