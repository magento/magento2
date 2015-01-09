<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Controller\Adminhtml\Customer;

/**
 *  Class to invalidate tokens for customers
 */
class InvalidateToken extends \Magento\Customer\Controller\Adminhtml\Index
{
    /**
     * Reset customer's tokens handler
     *
     * @return void
     */
    public function execute()
    {
        if ($customerId = $this->getRequest()->getParam('customer_id')) {
            try {
                /** @var \Magento\Integration\Service\V1\CustomerTokenService $tokenService */
                $tokenService = $this->_objectManager->get('Magento\Integration\Service\V1\CustomerTokenService');
                $tokenService->revokeCustomerAccessToken($customerId);
                $this->messageManager->addSuccess(__('You have revoked the customer\'s tokens.'));
                $this->_redirect('customer/index/edit', ['id' => $customerId, '_current' => true]);
                return;
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_redirect('customer/index/edit', ['id' => $customerId, '_current' => true]);
                return;
            }
        }
        $this->messageManager->addError(__('We can\'t find a customer to revoke.'));
        $this->_redirect('customer/index/index');
    }
}
