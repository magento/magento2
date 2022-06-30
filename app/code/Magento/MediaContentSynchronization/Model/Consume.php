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
use Magento\MediaContentApi\Api\Data\ContentIdentityInterfaceFactory;
use Magento\MediaContentSynchronizationApi\Api\SynchronizeIdentitiesInterface;
use Magento\MediaContentSynchronizationApi\Api\SynchronizeInterface;

/**
 * Media content synchronization queue consumer.
 */
class Consume
{
    private const ENTITY_TYPE = 'entityType';
    private const ENTITY_ID = 'entityId';
    private const FIELD = 'field';

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var ContentIdentityInterfaceFactory
     */
    private $contentIdentityFactory;

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
     * @param ContentIdentityInterfaceFactory $contentIdentityFactory
     * @param SynchronizeInterface $synchronize
     * @param SynchronizeIdentitiesInterface $synchronizeIdentities
     */
    public function __construct(
        SerializerInterface $serializer,
        ContentIdentityInterfaceFactory $contentIdentityFactory,
        SynchronizeInterface $synchronize,
        SynchronizeIdentitiesInterface $synchronizeIdentities
    ) {
        $this->serializer = $serializer;
        $this->contentIdentityFactory = $contentIdentityFactory;
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
        $identities = $this->serializer->unserialize($operation->getSerializedData());

        if (empty($identities)) {
            $this->synchronize->execute();
            return;
        }

        $contentIdentities = [];
        foreach ($identities as $identity) {
            $contentIdentities[] = $this->contentIdentityFactory->create(
                [
                    self::ENTITY_TYPE => $identity[self::ENTITY_TYPE],
                    self::ENTITY_ID => $identity[self::ENTITY_ID],
                    self::FIELD => $identity[self::FIELD]
                ]
            );
        }
        $this->synchronizeIdentities->execute($contentIdentities);
    }
}
