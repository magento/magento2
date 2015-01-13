<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Account;

class Logout extends \Magento\Customer\Controller\Account
{
    /**
     * Customer logout action
     *
     * @return void
     */
    public function execute()
    {
        $lastCustomerId = $this->_getSession()->getId();
        $this->_getSession()->logout()->setBeforeAuthUrl(
            $this->_redirect->getRefererUrl()
        )->setLastCustomerId(
            $lastCustomerId
        );

        $this->_redirect('*/*/logoutSuccess');
    }
}
