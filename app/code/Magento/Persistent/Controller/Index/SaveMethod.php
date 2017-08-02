<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Persistent\Controller\Index;

use Magento\Persistent\Controller\Index;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class \Magento\Persistent\Controller\Index\SaveMethod
 *
 * @since 2.0.0
 */
class SaveMethod extends Index
{
    /**
     * Save onepage checkout method to be register
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @since 2.0.0
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
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($checkoutUrl . (strpos($checkoutUrl, '?') ? '&' : '?') . 'register');
        return $resultRedirect;
    }
}
