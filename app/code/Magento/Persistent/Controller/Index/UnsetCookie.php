<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Persistent\Controller\Index;

use Magento\Persistent\Controller\Index;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class \Magento\Persistent\Controller\Index\UnsetCookie
 *
 * @since 2.0.0
 */
class UnsetCookie extends Index
{
    /**
     * Unset persistent cookie action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @since 2.0.0
     */
    public function execute()
    {
        if ($this->sessionHelper->isPersistent()) {
            $this->cleanup();
        }
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('customer/account/login');
        return $resultRedirect;
    }

    /**
     * Revert all persistent data
     *
     * @return $this
     * @since 2.0.0
     */
    protected function cleanup()
    {
        $this->_eventManager->dispatch('persistent_session_expired');
        $this->customerSession->setCustomerId(null)->setCustomerGroupId(null);
        if ($this->clearCheckoutSession) {
            $this->checkoutSession->clearStorage();
        }
        $this->sessionHelper->getSession()->removePersistentCookie();
        return $this;
    }
}
