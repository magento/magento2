<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;

class View extends \Magento\Sales\Controller\Adminhtml\Order
{
    /**
     * View order detail
     *
     * @return void
     */
    public function execute()
    {
        $order = $this->_initOrder();
        if ($order) {
            try {
                $this->_initAction();
                $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Orders'));
            } catch (\Magento\Framework\App\Action\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_redirect('sales/order/index');
                return;
            } catch (\Exception $e) {
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                $this->messageManager->addError(__('Exception occurred during order load'));
                $this->_redirect('sales/order/index');
                return;
            }
            $this->_view->getPage()->getConfig()->getTitle()->prepend(sprintf("#%s", $order->getRealOrderId()));
            $this->_view->renderLayout();
        }
    }
}
