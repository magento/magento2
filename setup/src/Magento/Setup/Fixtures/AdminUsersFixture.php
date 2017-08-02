<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Fixtures;

/**
 * Generate admin users
 *
 * Support the following format:
 * <!-- Number of admin users -->
 * <admin_users>{int}</admin_users>
 * @since 2.2.0
 */
class AdminUsersFixture extends Fixture
{
    /**
     * @var int
     * @since 2.2.0
     */
    protected $priority = 5;

    /**
     * @var \Magento\User\Model\UserFactory
     * @since 2.2.0
     */
    private $userFactory;

    /**
     * @var \Magento\Authorization\Model\RoleFactory
     * @since 2.2.0
     */
    private $roleFactory;

    /**
     * @var \Magento\User\Model\ResourceModel\User\CollectionFactory
     * @since 2.2.0
     */
    private $userCollectionFactory;

    /**
     * @param \Magento\User\Model\UserFactory $userFactory
     * @param \Magento\User\Model\ResourceModel\User\CollectionFactory $userCollectionFactory
     * @param \Magento\Authorization\Model\RoleFactory $roleFactory
     * @param FixtureModel $fixtureModel
     * @since 2.2.0
     */
    public function __construct(
        \Magento\User\Model\UserFactory $userFactory,
        \Magento\User\Model\ResourceModel\User\CollectionFactory $userCollectionFactory,
        \Magento\Authorization\Model\RoleFactory $roleFactory,
        FixtureModel $fixtureModel
    ) {
        parent::__construct($fixtureModel);
        $this->userFactory = $userFactory;
        $this->roleFactory = $roleFactory;
        $this->userCollectionFactory = $userCollectionFactory;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function execute()
    {
        $adminUsersNumber = $this->fixtureModel->getValue('admin_users', 0);
        $adminUsersStartIndex = $this->userCollectionFactory->create()->getSize();

        if ($adminUsersStartIndex >= $adminUsersNumber) {
            return;
        }

        $defaultAdminUser = $this->userFactory->create()->loadByUsername('admin');
        $defaultAdminRole = $this->roleFactory->create()->load($defaultAdminUser->getAclRole());

        for ($i = $adminUsersStartIndex; $i <= $adminUsersNumber; $i++) {
            $adminUser = $this->userFactory->create();
            $adminUser
                ->setEmail('admin' . $i . '@example.com')
                ->setFirstName('Firstname')
                ->setLastName('Lastname')
                ->setUserName('admin' . $i)
                ->setPassword('123123q')
                ->setIsActive(1);
            $adminUser->save();

            $role = $this->roleFactory->create();
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
     * @since 2.2.0
     */
    public function getActionTitle()
    {
        return 'Generating admin users';
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function introduceParamLabels()
    {
        return [
            'admin_users' => 'Admin Users'
        ];
    }
}
