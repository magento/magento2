<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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

        $resultPage->getLayout()->initMessages();
        return $resultPage;
    }
}
