<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml\Index;

class MassAssignGroup extends \Magento\Customer\Controller\Adminhtml\Index
{
    /**
     * Customer mass assign group action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $customerIds = $this->getRequest()->getParam('customer');
        $customersUpdated = $this->actUponMultipleCustomers(
            function ($customerId) {
                // Verify customer exists
                $customer = $this->_customerRepository->getById($customerId);
                $customer->setGroupId($this->getRequest()->getParam('group'));
                $this->_customerRepository->save($customer);
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
