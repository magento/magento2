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
use Mtf\TestCase\Injectable;
use Magento\User\Test\Page\Adminhtml\UserEdit;
use Magento\User\Test\Page\Adminhtml\UserIndex;

/**
 * Revoke all access tokens for admin without tokens.
 *
 * Test Flow:
 *
 * Preconditions:
 * 1. Tokens are not generated for admin.
 *
 * Steps:
 * 1. Open Backend.
 * 2. Open System > All Users.
 * 3. Open admin from the user grid.
 * 4. Click button Force Sign-in.
 * 5. Click Ok on popup window.
 * 6. Perform all asserts.
 *
 * @group Web_API_Framework_(PS)
 * @ZephyrId MAGETWO-29675
 */
class RevokeAllAccessTokensForAdminWithoutTokensTest extends Injectable
{
    /**
     * User Index page.
     *
     * @var UserIndex
     */
    protected $userIndex;

    /**
     * User Edit page.
     *
     * @var UserEdit
     */
    protected $userEdit;

    /**
     * Setup necessary data for test.
     *
     * @param UserIndex $userIndex
     * @param UserEdit $userEdit
     * @return void
     */
    public function __inject(
        UserIndex $userIndex,
        UserEdit $userEdit
    ) {
        $this->userIndex = $userIndex;
        $this->userEdit = $userEdit;
    }

    /**
     * Run Revoke all access tokens for admin without tokens test.
     *
     * @param User $user
     * @return void
     */
    public function test(User $user)
    {
        // Preconditions:
        $user->persist();
        // Steps:
        $this->userIndex->open();
        $this->userIndex->getUserGrid()->searchAndOpen(['username' => $user->getUsername()]);
        $this->userEdit->getPageActions()->forceSignIn();
    }
}
