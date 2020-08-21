<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContentSynchronization\Model;

use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\MediaContentSynchronizationApi\Api\SynchronizeIdentitiesInterface;
use Magento\MediaContentSynchronizationApi\Api\SynchronizeInterface;

/**
 * Media content synchronization queue consumer.
 */
class Consume
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var SynchronizeInterface
     */
    private $synchronize;

    /**
     * @var SynchronizeIdentitiesInterface
     */
    private $synchronizeIdentities;

    /**
     * @param SerializerInterface $serializer
     * @param SynchronizeInterface $synchronize
     * @param SynchronizeIdentitiesInterface $synchronizeIdentities
     */
    public function __construct(
        SerializerInterface $serializer,
        SynchronizeInterface $synchronize,
        SynchronizeIdentitiesInterface $synchronizeIdentities
    ) {
        $this->serializer = $serializer;
        $this->synchronize = $synchronize;
        $this->synchronizeIdentities = $synchronizeIdentities;
    }

    /**
     * Run media files synchronization.
     *
     * @param OperationInterface $operation
     * @throws LocalizedException
     */
    public function execute(OperationInterface $operation) : void
    {
        $serializedData = $operation->getSerializedData();
        $identities = $this->serializer->unserialize($serializedData);

        if (!empty($identities)) {
            $this->synchronizeIdentities->execute($identities);
        } else {
            $this->synchronize->execute();
        }
    }
}
