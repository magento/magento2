<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order;

use Magento\Framework\Model\Resource\Db\Collection\AbstractCollection;

class MassCancel extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
{
    /**
     * Cancel selected orders
     *
     * @param AbstractCollection $collection
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected function massAction(AbstractCollection $collection)
    {
        $countCancelOrder = 0;
        $countNonCancelOrder = 0;
        foreach ($collection->getItems() as $order) {
            if ($order->canCancel()) {
                $order->cancel()->save();
                $countCancelOrder++;
            } else {
                $countNonCancelOrder++;
            }
        }
        if ($countNonCancelOrder) {
            if ($countCancelOrder) {
                $this->messageManager->addError(__('%1 order(s) cannot be canceled.', $countNonCancelOrder));
            } else {
                $this->messageManager->addError(__('You cannot cancel the order(s).'));
            }
        }
        if ($countCancelOrder) {
            $this->messageManager->addSuccess(__('We canceled %1 order(s).', $countCancelOrder));
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('sales/*/');
        return $resultRedirect;
    }
}
