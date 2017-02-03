<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Fixtures;

/**
 * Class for generating admin users.
 */
class AdminUsersFixture extends Fixture
{
    /**
     * @var int
     */
    protected $priority = 5;

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $adminUsersNumber = $this->fixtureModel->getValue('admin_users', 0);
        if (!$adminUsersNumber) {
            return;
        }

        /** @var \Magento\User\Model\UserFactory $adminUserFactory */
        $adminUserFactory = $this->fixtureModel->getObjectManager()->create(\Magento\User\Model\UserFactory::class);

        /** @var \Magento\Authorization\Model\RoleFactory $roleFactory */
        $roleFactory = $this->fixtureModel->getObjectManager()->create(\Magento\Authorization\Model\RoleFactory::class);

        $defaultAdminUser = $adminUserFactory->create()->loadByUsername('admin');
        $defaultAdminRole = $roleFactory->create()->load($defaultAdminUser->getAclRole());

        for ($i = 1; $i <= $adminUsersNumber; $i++) {
            $adminUser = $adminUserFactory->create();
            $adminUser
                ->setEmail('admin' . $i . '@example.com')
                ->setFirstName('Firstname')
                ->setLastName('Lastname')
                ->setUserName('admin' . $i)
                ->setPassword('123123q')
                ->setIsActive(1);
            $adminUser->save();

            $role = $roleFactory->create();
            $role
                ->setUserId($adminUser->getId())
                ->setRoleName('admin')
                ->setRoleType($defaultAdminRole->getRoleType())
                ->setUserType($defaultAdminRole->getUserType())
                ->setTreeLevel($defaultAdminRole->getTreeLevel())
                ->setSortOrder($defaultAdminRole->getSortOrder())
                ->setParentId(1);
            $role->save();
        }

    }

    /**
     * {@inheritdoc}
     */
    public function getActionTitle()
    {
        return 'Generating admin users';
    }

    /**
     * {@inheritdoc}
     */
    public function introduceParamLabels()
    {
        return [
            'customers' => 'Admin Users'
        ];
    }
}
