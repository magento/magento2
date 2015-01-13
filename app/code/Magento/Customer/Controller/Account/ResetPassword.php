<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Account;

class ResetPassword extends \Magento\Customer\Controller\Account
{
    /**
     * Display reset forgotten password form
     *
     * User is redirected on this action when he clicks on the corresponding link in password reset confirmation email
     *
     * @return void
     */
    public function execute()
    {
        $this->_forward('createPassword');
    }
}
