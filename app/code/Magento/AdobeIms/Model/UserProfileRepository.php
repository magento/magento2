<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdobeIms\Model;

use Exception;
use Magento\AdobeImsApi\Api\Data\UserProfileInterface;
use Magento\AdobeImsApi\Api\Data\UserProfileInterfaceFactory;
use Magento\AdobeImsApi\Api\UserProfileRepositoryInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

/**
 * Represent user profile repository
 */
class UserProfileRepository implements UserProfileRepositoryInterface
{
    private const ADMIN_USER_ID = 'admin_user_id';

    /**
     * @var ResourceModel\UserProfile
     */
    private $resource;

    /**
     * @var UserProfileInterfaceFactory
     */
    private $entityFactory;

    /**
     * @var array
     */
    private $loadedEntities = [];

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * UserProfileRepository constructor.
     *
     * @param ResourceModel\UserProfile $resource
     * @param UserProfileInterfaceFactory $entityFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        ResourceModel\UserProfile $resource,
        UserProfileInterfaceFactory $entityFactory,
        LoggerInterface $logger
    ) {
        $this->resource = $resource;
        $this->entityFactory = $entityFactory;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function save(UserProfileInterface $entity): void
    {
        try {
            $this->resource->save($entity);
            $this->loadedEntities[$entity->getId()] = $entity;
        } catch (Exception $exception) {
            $this->logger->critical($exception);
            throw new CouldNotSaveException(__('Could not save user profile.'), $exception);
        }
    }

    /**
     * @inheritdoc
     */
    public function get(int $entityId): UserProfileInterface
    {
        if (isset($this->loadedEntities[$entityId])) {
            return $this->loadedEntities[$entityId];
        }

        $entity = $this->entityFactory->create();
        $this->resource->load($entity, $entityId);
        if (!$entity->getId()) {
            throw new NoSuchEntityException(__('Could not find user profile id: %id.', ['id' => $entityId]));
        }

        return $this->loadedEntities[$entity->getId()] = $entity;
    }

    /**
     * @inheritdoc
     */
    public function getByUserId(int $userId): UserProfileInterface
    {
        $entity = $this->entityFactory->create();
        $this->resource->load($entity, $userId, self::ADMIN_USER_ID);
        if (!$entity->getId()) {
            throw new NoSuchEntityException(__('Could not find user profile id: %id.', ['id' => $userId]));
        }

        return $this->loadedEntities[$entity->getId()] = $entity;
    }
}
