<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Controller\Creditcard;

class NewCard extends \Magento\Braintree\Controller\MyCreditCards
{
    /**
     *  New credit card form action
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        if ($navigationBlock = $resultPage->getLayout()->getBlock('customer_account_navigation')) {
            $navigationBlock->setActive('braintree/creditcard/index');
        }
        $resultPage->getConfig()->getTitle()->set(__('New Credit Card'));

        return $resultPage;
    }
}
