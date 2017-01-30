<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Controller\Creditcard;

class Index extends \Magento\Braintree\Controller\MyCreditCards
{
    /**
     * Display a list of credit cards
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
        if ($block = $resultPage->getLayout()->getBlock('customer_creditcard_management')) {
            $block->setRefererUrl($this->_redirect->getRefererUrl());
        }
        $resultPage->getConfig()->getTitle()->set(__('My Credit Cards'));
        return $resultPage;
    }
}
