<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Controller\Adminhtml\Customer;

use Magento\Integration\Api\CustomerTokenServiceInterface;

/**
 *  Class to invalidate tokens for customers
 */
class InvalidateToken extends \Magento\Customer\Controller\Adminhtml\Index
{
    /**
     * @var CustomerTokenServiceInterface
     */
    protected $tokenService;

    /**
     * Inject dependencies.
     *
     * @param CustomerTokenServiceInterface $tokenService
     */
    public function __construct(CustomerTokenServiceInterface $tokenService)
    {
        $this->tokenService = $tokenService;
    }

    /**
     * Reset customer's tokens handler
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($customerId = $this->getRequest()->getParam('customer_id')) {
            try {
                $this->tokenService->revokeCustomerAccessToken($customerId);
                $this->messageManager->addSuccess(__('You have revoked the customer\'s tokens.'));
                $resultRedirect->setPath('customer/index/edit', ['id' => $customerId, '_current' => true]);
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $resultRedirect->setPath('customer/index/edit', ['id' => $customerId, '_current' => true]);
            }
        } else {
            $this->messageManager->addError(__('We can\'t find a customer to revoke.'));
            $resultRedirect->setPath('customer/index/index');
        }
        return $resultRedirect;
    }
}
