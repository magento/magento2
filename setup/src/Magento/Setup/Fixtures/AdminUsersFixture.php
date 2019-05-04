<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Fixtures;

use Magento\Authorization\Model\Acl\Role\Group;
use Magento\Authorization\Model\RoleFactory;
use Magento\Authorization\Model\RulesFactory;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Acl\RootResource;
use Magento\User\Model\ResourceModel\User\CollectionFactory as UserCollectionFactory;
use Magento\User\Model\UserFactory;

/**
 * Generate admin users
 *
 * Support the following format:
 * <!-- Number of admin users -->
 * <admin_users>{int}</admin_users>
 */
class AdminUsersFixture extends Fixture
{
    /**
     * @var int
     */
    protected $priority = 5;

    /**
     * @var UserFactory
     */
    private $userFactory;

    /**
     * @var RoleFactory
     */
    private $roleFactory;

    /**
     * @var UserCollectionFactory
     */
    private $userCollectionFactory;

    /**
     * @var RulesFactory
     */
    private $rulesFactory;

    /**
     * @var RootResource
     */
    private $rootResource;

    /**
     * @param FixtureModel $fixtureModel
     * @param UserFactory $userFactory
     * @param UserCollectionFactory $userCollectionFactory
     * @param RoleFactory $roleFactory
     * @param RulesFactory $rulesFactory
     * @param RootResource $rootResource
     */
    public function __construct(
        FixtureModel $fixtureModel,
        UserFactory $userFactory,
        UserCollectionFactory $userCollectionFactory,
        RoleFactory $roleFactory,
        RulesFactory $rulesFactory,
        RootResource $rootResource
    ) {
        parent::__construct($fixtureModel);
        $this->userFactory = $userFactory;
        $this->roleFactory = $roleFactory;
        $this->userCollectionFactory = $userCollectionFactory;
        $this->rulesFactory = $rulesFactory;
        $this->rootResource = $rootResource;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $adminUsersNumber = $this->fixtureModel->getValue('admin_users', 0);
        $adminUsersStartIndex = $this->userCollectionFactory->create()->getSize();

        if ($adminUsersStartIndex >= $adminUsersNumber) {
            return;
        }

        $role = $this->createAdministratorRole();

        for ($i = $adminUsersStartIndex; $i <= $adminUsersNumber; $i++) {
            $adminUser = $this->userFactory->create();
            $adminUser->setRoleId($role->getId())
                ->setEmail('admin' . $i . '@example.com')
                ->setFirstName('Firstname')
                ->setLastName('Lastname')
                ->setUserName('admin' . $i)
                ->setPassword('123123q')
                ->setIsActive(1);
            $adminUser->save();
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
            'admin_users' => 'Admin Users'
        ];
    }

    /**
     * Create administrator role with all privileges.
     *
     * @return \Magento\Authorization\Model\Role
     */
    private function createAdministratorRole()
    {
        $role = $this->roleFactory->create();
        $role->setParentId(0)
            ->setTreeLevel(1)
            ->setSortOrder(1)
            ->setRoleType(Group::ROLE_TYPE)
            ->setUserId(0)
            ->setUserType(UserContextInterface::USER_TYPE_ADMIN)
            ->setRoleName('Example Administrator');
        $role->save();

        /** @var \Magento\Authorization\Model\Rules $rule */
        $rule = $this->rulesFactory->create();
        $rule->setRoleId($role->getId())
            ->setResourceId($this->rootResource->getId())
            ->setPrivilegies(null)
            ->setPermission('allow');
        $rule->save();

        return $role;
    }
}
