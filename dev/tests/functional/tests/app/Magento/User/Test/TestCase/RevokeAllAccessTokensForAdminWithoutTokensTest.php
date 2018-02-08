<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\TestCase;

use Magento\User\Test\Fixture\User;
use Magento\User\Test\Page\Adminhtml\UserEdit;
use Magento\User\Test\Page\Adminhtml\UserIndex;
use Magento\Mtf\TestCase\Injectable;

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
    /* tags */
    const MVP = 'no';
    const DOMAIN = 'PS';
    /* end tags */

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
