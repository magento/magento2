<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\User\Model;

use Magento\User\Model\ResourceModel\User\Collection;
use Magento\User\Model\ResourceModel\User\CollectionFactory as UserCollectionFactory;

/**
 * Class GetAdminUserList provides list of all admin users in the system
 */
class GetAdminUserList
{
    /**
     * @var UserCollectionFactory
     */
    private $userCollectionFactory;

    /**
     * GetAdminUserList constructor
     *
     * @param UserCollectionFactory $userCollectionFactory
     */
    public function __construct(
        UserCollectionFactory $userCollectionFactory
    ) {
        $this->userCollectionFactory = $userCollectionFactory;
    }

    /**
     * Provides admin users list
     *
     * @return Collection
     */
    public function execute(): Collection
    {
        return $this->userCollectionFactory->create()->addFieldToSelect('username')->load();
    }
}
