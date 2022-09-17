<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdminAdobeIms\Service;

use Magento\Authorization\Model\UserContextInterface;
use Magento\JwtUserToken\Api\Data\Revoked;
use Magento\JwtUserToken\Api\RevokedRepositoryInterface;
use Magento\User\Model\ResourceModel\User\Collection;
use Magento\User\Model\ResourceModel\User\CollectionFactory;

class UpdateTokensService
{
    /**
     * @var RevokedRepositoryInterface
     */
    private RevokedRepositoryInterface $revokedRepo;

    /**
     * @var Collection
     */
    private Collection $adminUserCollection;

    /**
     * @param RevokedRepositoryInterface $revokedRepo
     * @param CollectionFactory $adminUserCollectionFactory
     */
    public function __construct(RevokedRepositoryInterface $revokedRepo, CollectionFactory $adminUserCollectionFactory)
    {
        $this->revokedRepo = $revokedRepo;
        $this->adminUserCollection = $adminUserCollectionFactory->create();
    }

    /**
     * Token invalidation for the admin users
     *
     * @return void
     */
    public function execute(): void
    {
        $adminUsers = $this->adminUserCollection->getItems();
        foreach ($adminUsers as $adminUser) {
            //Invalidating all tokens issued before current datetime.
            $this->revokedRepo->saveRevoked(
                new Revoked((int) UserContextInterface::USER_TYPE_ADMIN, (int) $adminUser->getId(), time())
            );
        }
    }
}
