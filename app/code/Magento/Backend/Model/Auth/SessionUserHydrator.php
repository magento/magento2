<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Backend\Model\Auth;

use Magento\Backend\Spi\SessionUserHydratorInterface;
use Magento\User\Model\User;
use Magento\Authorization\Model\Role;
use Magento\Authorization\Model\RoleFactory;

/**
 * @inheritDoc
 */
class SessionUserHydrator implements SessionUserHydratorInterface
{
    /**
     * @var RoleFactory
     */
    private $roleFactory;

    /**
     * @param RoleFactory $roleFactory
     */
    public function __construct(RoleFactory $roleFactory)
    {
        $this->roleFactory = $roleFactory;
    }

    /**
     * @inheritDoc
     */
    public function extract(User $user): array
    {
        return ['data' => $user->getData(), 'role_data' => $user->getRole()->getData()];
    }

    /**
     * @inheritDoc
     */
    public function hydrate(User $target, array $data): void
    {
        $target->setData($data['data']);
        /** @var Role $role */
        $role = $this->roleFactory->create();
        $role->setData($data['role_data']);
        $target->setData('extracted_role', $role);
        $target->getRole();
    }
}
