<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order;


class AddressSave extends \Magento\Sales\Controller\Adminhtml\Order
{
    /**
     * Save order address
     *
     * @return void
     */
    public function execute()
    {
        $addressId = $this->getRequest()->getParam('address_id');
        $address = $this->_objectManager->create('Magento\Sales\Model\Order\Address')->load($addressId);
        $data = $this->getRequest()->getPost();
        if ($data && $address->getId()) {
            $address->addData($data);
            try {
                $address->save();
                $this->messageManager->addSuccess(__('You updated the order address.'));
                $this->_redirect('sales/*/view', ['order_id' => $address->getParentId()]);
                return;
            } catch (\Magento\Framework\Model\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong updating the order address.'));
            }
            $this->_redirect('sales/*/address', ['address_id' => $address->getId()]);
        } else {
            $this->_redirect('sales/*/');
        }
    }
}
