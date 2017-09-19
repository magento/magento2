<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\TestStep;

use Magento\Mtf\TestStep\TestStepInterface;
use Magento\User\Test\Fixture\User;
use Magento\Backend\Test\Page\Adminhtml\SystemAccount;

/**
 * Switch admin user's interface locale.
 */
class SwitchInterfaceLocaleForUserStep implements TestStepInterface
{
    /**
     * Current Admin user
     *
     * @var User
     */
    private $user;

    /**
     * Default Admin user
     *
     * @var User
     */
    private $defaultUser;

    /**
     * Admin Account Page
     *
     * @var SystemAccount
     */
    private $systemAccount;

    /**
     * @constructor
     * @param SystemAccount $systemAccount
     * @param User $user
     * @param User $defaultUser
     */
    public function __construct(SystemAccount $systemAccount, User $user, User $defaultUser)
    {
        $this->systemAccount = $systemAccount;
        $this->user = $user;
        $this->defaultUser = $defaultUser;
    }

    /**
     * Switch Interface locale for the current Admin User and save.
     *
     * @return void
     */
    public function run()
    {
        $this->systemAccount->open();
        $this->systemAccount->getSystemAccountEditForm()->fill($this->user);
        $this->systemAccount->getFormPageActions()->save();
    }

    /**
     * Reset Interface Locale to default value.
     *
     * @return void
     */
    public function cleanup()
    {
        $this->systemAccount->open();
        $this->systemAccount->getSystemAccountEditForm()->fill($this->defaultUser);
        $this->systemAccount->getFormPageActions()->save();
    }
}
