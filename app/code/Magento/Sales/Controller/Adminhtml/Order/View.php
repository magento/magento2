<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;

class View extends \Magento\Sales\Controller\Adminhtml\Order
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Sales::actions_view';

    /**
     * View order detail
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $order = $this->_initOrder();
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($order) {
            try {
                $resultPage = $this->_initAction();
                $resultPage->getConfig()->getTitle()->prepend(__('Orders'));
            } catch (\Exception $e) {
                $this->logger->critical($e);
                $this->messageManager->addErrorMessage(__('Exception occurred during order load'));
                $resultRedirect->setPath('sales/order/index');
                return $resultRedirect;
            }
            $resultPage->getConfig()->getTitle()->prepend(sprintf("#%s", $order->getIncrementId()));
            return $resultPage;
        }
        $resultRedirect->setPath('sales/*/');
        return $resultRedirect;
    }
}
