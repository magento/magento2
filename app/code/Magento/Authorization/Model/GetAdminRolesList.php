<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Authorization\Model;

use Magento\Authorization\Model\ResourceModel\Role\Grid\CollectionFactory;

/**
 * Class GetAdminRolesList provides information about all existing group roles
 */
class GetAdminRolesList
{
    /**
     * @var CollectionFactory
     */
    private $rolesGridCollectionFactory;

    /**
     * @param CollectionFactory $rolesGridCollectionFactory
     */
    public function __construct(
        CollectionFactory $rolesGridCollectionFactory
    ) {
        $this->rolesGridCollectionFactory = $rolesGridCollectionFactory;
    }

    /**
     * Provides information about all existing group roles
     *
     * @return array
     */
    public function execute(): array
    {
        return $this->rolesGridCollectionFactory->create()
            ->addFieldToSelect('role_id')
            ->addFieldToSelect('role_name')
            ->load()
            ->toArray()['items'];
    }
}
