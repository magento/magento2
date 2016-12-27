<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\TestCase;

use Magento\User\Test\Fixture\User;
use Magento\User\Test\Page\Adminhtml\UserEdit;
use Magento\User\Test\Page\Adminhtml\UserIndex;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestCase\Injectable;

/**
 * Test Creation for CreateAdminUserEntityTest
 *
 * Test Flow:
 * 1. Log in as default admin user
 * 2. Go to System-Permissions-All Users
 * 3. Press "+" button to start create new admin user
 * 4. Fill in all data according to data set
 * 5. Save user
 * 6. Perform assertions
 *
 * @group ACL
 * @ZephyrId MAGETWO-25699
 */
class CreateAdminUserEntityTest extends Injectable
{
    /* tags */
    const MVP = 'no';
    const TEST_TYPE = 'extended_acceptance_test';
    const STABLE = 'no';
    /* end tags */

    /**
     * User grid page
     *
     * @var UserIndex
     */
    protected $userIndexPage;

    /**
     * User new/edit page
     *
     * @var UserEdit
     */
    protected $userEditPage;

    /**
     * Factory for Fixtures
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Preconditions for test
     *
     * @param FixtureFactory $fixtureFactory
     * @return array
     */
    public function __prepare(FixtureFactory $fixtureFactory)
    {
        $this->fixtureFactory = $fixtureFactory;
        $adminUser = $fixtureFactory->createByCode('user', ['dataset' => 'custom_admin']);
        $adminUser->persist();

        return ['adminUser' => $adminUser];
    }

    /**
     * Setup necessary data for test
     *
     * @param UserIndex $userIndex
     * @param UserEdit $userEdit
     * @return void
     */
    public function __inject(
        UserIndex $userIndex,
        UserEdit $userEdit
    ) {
        $this->userIndexPage = $userIndex;
        $this->userEditPage = $userEdit;
    }

    /**
     * @param User $user
     * @param User $adminUser
     * @param string $isDuplicated
     * @return array
     */
    public function test(User $user, User $adminUser, $isDuplicated = null)
    {
        // Prepare data
        if ($isDuplicated !== null) {
            $data = $user->getData();
            $data[$isDuplicated] = $adminUser->getData($isDuplicated);
            $data['role_id'] = ['role' => $user->getDataFieldConfig('role_id')['source']->getRole()];
            $user = $this->fixtureFactory->createByCode('user', ['data' => $data]);
        }

        // Steps
        $this->userIndexPage->open();
        $this->userIndexPage->getPageActions()->addNew();
        $this->userEditPage->getUserForm()->fill($user);
        $this->userEditPage->getPageActions()->save();

        return ['customAdmin' => $user];
    }
}
