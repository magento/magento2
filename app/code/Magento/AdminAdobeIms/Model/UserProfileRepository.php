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
class UserProfileRepository extends \Magento\AdobeIms\Model\UserProfileRepository implements UserProfileRepositoryInterface
{
    private const ADOBE_USER_ID = 'adobe_user_id';

    private UserProfile $resource;
    private UserProfileInterfaceFactory $entityFactory;

    public function __construct(
        UserProfile                 $resource,
        UserProfileInterfaceFactory $entityFactory,
        LoggerInterface             $logger
    ) {
        parent::__construct($resource, $entityFactory, $logger);
        $this->resource = $resource;
        $this->entityFactory = $entityFactory;
    }

    public function getByAdobeUserId($adobeUserId): UserProfileInterface
    {
        $entity = $this->entityFactory->create();
        $this->resource->load($entity, $adobeUserId, self::ADOBE_USER_ID);

        return $entity;
    }
}
