<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Persistent\Controller\Index;

class UnsetCookie extends \Magento\Persistent\Controller\Index
{
    /**
     * Revert all persistent data
     *
     * @return $this
     */
    protected function _cleanup()
    {
        $this->_eventManager->dispatch('persistent_session_expired');
        $this->_customerSession->setCustomerId(null)->setCustomerGroupId(null);
        if ($this->_clearCheckoutSession) {
            $this->_checkoutSession->clearStorage();
        }
        $this->_getHelper()->getSession()->removePersistentCookie();
        return $this;
    }

    /**
     * Unset persistent cookie action
     *
     * @return void
     */
    public function execute()
    {
        if ($this->_getHelper()->isPersistent()) {
            $this->_cleanup();
        }
        $this->_redirect('customer/account/login');
        return;
    }
}
