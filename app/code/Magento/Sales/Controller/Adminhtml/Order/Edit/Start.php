<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order\Edit;

class Start extends \Magento\Sales\Controller\Adminhtml\Order\Create\Start
{
    /**
     * Acl check for admin
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Sales::actions_edit');
    }

    /**
     * Start edit order initialization
     *
     * @return void
     */
    public function execute()
    {
        $this->_getSession()->clearStorage();
        $orderId = $this->getRequest()->getParam('order_id');
        $order = $this->_objectManager->create('Magento\Sales\Model\Order')->load($orderId);

        try {
            if ($order->getId()) {
                $this->_getSession()->setUseOldShippingMethod(true);
                $this->_getOrderCreateModel()->initFromOrder($order);
                $this->_redirect('sales/*');
            } else {
                $this->_redirect('sales/order/');
            }
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            $this->_redirect('sales/order/view', ['order_id' => $orderId]);
        } catch (\Exception $e) {
            $this->messageManager->addException($e, $e->getMessage());
            $this->_redirect('sales/order/view', ['order_id' => $orderId]);
        }
    }
}
