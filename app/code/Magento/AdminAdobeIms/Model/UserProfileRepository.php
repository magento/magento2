<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Model;

use Magento\AdobeIms\Model\ResourceModel\UserProfile;
use Magento\AdobeImsApi\Api\Data\UserProfileInterface;
use Magento\AdobeImsApi\Api\Data\UserProfileInterfaceFactory;
use Magento\AdobeImsApi\Api\UserProfileRepositoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Represent user profile repository
 */
class UserProfileRepository extends \Magento\AdobeIms\Model\UserProfileRepository
    implements UserProfileRepositoryInterface
{
    private const ADOBE_USER_ID = 'adobe_user_id';

    /**
     * @var UserProfile
     */
    private UserProfile $resource;

    /**
     * @var UserProfileInterfaceFactory
     */
    private UserProfileInterfaceFactory $entityFactory;

    /**
     * @param UserProfile $resource
     * @param UserProfileInterfaceFactory $entityFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        UserProfile                 $resource,
        UserProfileInterfaceFactory $entityFactory,
        LoggerInterface             $logger
    ) {
        parent::__construct($resource, $entityFactory, $logger);
        $this->resource = $resource;
        $this->entityFactory = $entityFactory;
    }

    /**
     * Get user profile by adobe user ID
     *
     * @param string $adobeUserId
     * @return UserProfileInterface
     */
    public function getByAdobeUserId(string $adobeUserId): UserProfileInterface
    {
        $entity = $this->entityFactory->create();
        $this->resource->load($entity, $adobeUserId, self::ADOBE_USER_ID);

        return $entity;
    }
}
