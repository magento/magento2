<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml\Index;

class MassDelete extends \Magento\Customer\Controller\Adminhtml\Index
{
    /**
     * Customer mass delete action
     *
     * @return void
     */
    public function execute()
    {
        $customerIds = $this->getRequest()->getParam('customer');
        $customersDeleted = $this->actUponMultipleCustomers(
            function ($customerId) {
                $this->_customerRepository->deleteById($customerId);
            },
            $customerIds
        );
        if ($customersDeleted) {
            $this->messageManager->addSuccess(__('A total of %1 record(s) were deleted.', $customersDeleted));
        }
        $this->_redirect('customer/*/index');
    }
}
