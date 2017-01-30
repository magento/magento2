<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Controller\Creditcard;

class Delete extends \Magento\Braintree\Controller\MyCreditCards
{
    /**
     * Edit an existing credit card action
     *
     * @return \Magento\Framework\View\Result\Page|\Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        if ($this->hasToken()) {
            if (!$this->vault->storedCard($this->hasToken())) {
                $this->messageManager->addError(__('Credit card does not exist'));
                /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('braintree/creditcard/index');
                return $resultRedirect;
            }

            /** @var \Magento\Framework\View\Result\Page $resultPage */
            $resultPage = $this->resultPageFactory->create();
            if ($navigationBlock = $resultPage->getLayout()->getBlock('customer_account_navigation')) {
                $navigationBlock->setActive('braintree/creditcard/index');
            }
            if ($block = $resultPage->getLayout()->getBlock('customer_creditcard_management')) {
                $block->setRefererUrl($this->_redirect->getRefererUrl());
            }
            $resultPage->getConfig()->getTitle()->set(__('Delete Credit Card'));
            return $resultPage;
        } else {
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('braintree/creditcard/index');
            return $resultRedirect;
        }
    }
}
