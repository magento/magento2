<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order;

use Magento\Framework\Model\Resource\Db\Collection\AbstractCollection;

class MassHold extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
{
    /**
     * Hold selected orders
     *
     * @param AbstractCollection $collection
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected function massAction(AbstractCollection $collection)
    {
        $countHoldOrder = 0;
        foreach ($collection->getItems() as $order) {
            if (!$order->canHold()) {
                continue;
            }
            $order->hold();
            $order->save();
            $countHoldOrder++;
        }
        $countNonHoldOrder = $collection->count() - $countHoldOrder;

        if ($countNonHoldOrder && $countHoldOrder) {
            $this->messageManager->addError(__('%1 order(s) were not put on hold.', $countNonHoldOrder));
        } elseif ($countNonHoldOrder) {
            $this->messageManager->addError(__('No order(s) were put on hold.'));
        }

        if ($countHoldOrder) {
            $this->messageManager->addSuccess(__('You have put %1 order(s) on hold.', $countHoldOrder));
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('sales/*/');
        return $resultRedirect;
    }
}
