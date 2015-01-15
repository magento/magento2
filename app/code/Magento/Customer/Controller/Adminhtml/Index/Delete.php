<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml\Index;

use Magento\Customer\Controller\RegistryConstants;

class Delete extends \Magento\Customer\Controller\Adminhtml\Index
{
    /**
     * Delete customer action
     *
     * @return void
     */
    public function execute()
    {
        $this->_initCustomer();
        $customerId = $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
        if (!empty($customerId)) {
            try {
                $this->_customerRepository->deleteById($customerId);
                $this->messageManager->addSuccess(__('You deleted the customer.'));
            } catch (\Exception $exception) {
                $this->messageManager->addError($exception->getMessage());
            }
        }
        $this->_redirect('customer/index');
    }
}
