<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Account;

class ForgotPassword extends \Magento\Customer\Controller\Account
{
    /**
     * Forgot customer password page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getLayout()->getBlock('forgotPassword')->setEmailValue($this->_getSession()->getForgottenEmail());

        $this->_getSession()->unsForgottenEmail();

        return $resultPage;
    }
}
