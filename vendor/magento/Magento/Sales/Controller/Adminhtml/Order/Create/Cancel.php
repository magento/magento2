<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Sales\Controller\Adminhtml\Order\Create;


class Cancel extends \Magento\Sales\Controller\Adminhtml\Order\Create
{
    /**
     * Cancel order create
     *
     * @return void
     */
    public function execute()
    {
        if ($orderId = $this->_getSession()->getReordered()) {
            $this->_getSession()->clearStorage();
            $this->_redirect('sales/order/view', ['order_id' => $orderId]);
        } else {
            $this->_getSession()->clearStorage();
            $this->_redirect('sales/*');
        }
    }
}
