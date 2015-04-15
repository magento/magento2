<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Persistent\Controller\Index;

use Magento\Persistent\Controller\Index;

class SaveMethod extends Index
{
    /**
     * Save onepage checkout method to be register
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        if ($this->sessionHelper->isPersistent()) {
            $this->sessionHelper->getSession()->removePersistentCookie();
            if (!$this->customerSession->isLoggedIn()) {
                $this->customerSession->setCustomerId(null)->setCustomerGroupId(null);
            }
            $this->quoteManager->setGuest();
        }
        $checkoutUrl = $this->_redirect->getRefererUrl();
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath($checkoutUrl . (strpos($checkoutUrl, '?') ? '&' : '?') . 'register');
        return $resultRedirect;
    }
}
