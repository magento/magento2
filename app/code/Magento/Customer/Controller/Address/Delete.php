<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Address;

use Magento\Framework\App\Action\HttpGetActionInterface;

class Delete extends \Magento\Customer\Controller\Address implements HttpGetActionInterface
{
    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $addressId = $this->getRequest()->getParam('id', false);

        if ($addressId && $this->_formKeyValidator->validate($this->getRequest())) {
            $address = null;
            $customer = $this->_customerRepository->getById($this->_getSession()->getCustomerId());
            $addresses = $customer->getAddresses();

            try {
                foreach ($addresses as $key => $customerAddress) {
                    if ($customerAddress->getId() === $addressId) {
                        $address = $customerAddress;
                        unset($addresses[$key]);
                    }
                }
                if ($address !== null) {
                    $customer->setAddresses($addresses);
                    $this->_customerRepository->save($customer);
                    $this->messageManager->addSuccess(__('You deleted the address.'));
                } else {
                    $this->messageManager->addError(__('We can\'t delete the address right now.'));
                }
            } catch (\Exception $other) {
                $this->messageManager->addException($other, __('We can\'t delete the address right now.'));
            }
        }

        return $this->resultRedirectFactory->create()->setPath('*/*/index');
    }
}
