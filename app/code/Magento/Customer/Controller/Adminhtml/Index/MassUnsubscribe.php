<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml\Index;

class MassUnsubscribe extends \Magento\Customer\Controller\Adminhtml\Index
{
    /**
     * Customer mass unsubscribe action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $customerIds = $this->getRequest()->getParam('customer');
        $customersUpdated = $this->actUponMultipleCustomers(
            function ($customerId) {
                // Verify customer exists
                $this->_customerRepository->getById($customerId);
                $this->_subscriberFactory->create()->unsubscribeCustomerById($customerId);
            },
            $customerIds
        );
        if ($customersUpdated) {
            $this->messageManager->addSuccess(__('A total of %1 record(s) were updated.', $customersUpdated));
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('customer/*/index');
        return $resultRedirect;
    }
}
