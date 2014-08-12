<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\User\Test\TestCase;

use Magento\User\Test\Fixture\User;
use Magento\User\Test\Page\Adminhtml\UserEdit;
use Magento\User\Test\Page\Adminhtml\UserIndex;
use Mtf\Fixture\FixtureFactory;
use Mtf\TestCase\Injectable;

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
 * @group ACL_(MX)
 * @ZephyrId MAGETWO-23413
 */
class CreateAdminUserEntityTest extends Injectable
{
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
        $adminUser = $fixtureFactory->createByCode('user');
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
     * @return void
     */
    public function test(
        User $user,
        User $adminUser,
        $isDuplicated
    ) {
        // Prepare data
        if ($isDuplicated != '-') {
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
    }
}
