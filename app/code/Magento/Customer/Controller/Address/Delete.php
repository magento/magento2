<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Address;

class Delete extends \Magento\Customer\Controller\Address
{
    /**
     * @return void
     */
    public function execute()
    {
        $addressId = $this->getRequest()->getParam('id', false);

        if ($addressId) {
            try {
                $address = $this->_addressRepository->getById($addressId);
                if ($address->getCustomerId() === $this->_getSession()->getCustomerId()) {
                    $this->_addressRepository->deleteById($addressId);
                    $this->messageManager->addSuccess(__('The address has been deleted.'));
                } else {
                    $this->messageManager->addError(__('An error occurred while deleting the address.'));
                }
            } catch (\Exception $other) {
                $this->messageManager->addException($other, __('An error occurred while deleting the address.'));
            }
        }
        $this->getResponse()->setRedirect($this->_buildUrl('*/*/index'));
    }
}
