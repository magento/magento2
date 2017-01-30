<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order;

class AddressSave extends \Magento\Sales\Controller\Adminhtml\Order
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Sales::actions_edit';

    /**
     * Save order address
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $addressId = $this->getRequest()->getParam('address_id');
        /** @var $address \Magento\Sales\Api\Data\OrderAddressInterface|\Magento\Sales\Model\Order\Address */
        $address = $this->_objectManager->create('Magento\Sales\Api\Data\OrderAddressInterface')->load($addressId);
        $data = $this->getRequest()->getPostValue();
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data && $address->getId()) {
            $address->addData($data);
            try {
                $address->save();
                $this->_eventManager->dispatch(
                    'admin_sales_order_address_update',
                    [
                        'order_id' => $address->getParentId()
                    ]
                );
                $this->messageManager->addSuccess(__('You updated the order address.'));
                return $resultRedirect->setPath('sales/*/view', ['order_id' => $address->getParentId()]);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('We can\'t update the order address right now.'));
            }
            return $resultRedirect->setPath('sales/*/address', ['address_id' => $address->getId()]);
        } else {
            return $resultRedirect->setPath('sales/*/');
        }
    }
}
