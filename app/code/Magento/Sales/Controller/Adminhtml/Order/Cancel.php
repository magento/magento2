<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Sales\Controller\Adminhtml\Order;


class Cancel extends \Magento\Sales\Controller\Adminhtml\Order
{
    /**
     * Cancel order
     *
     * @return void
     */
    public function execute()
    {
        $order = $this->_initOrder();
        if ($order) {
            try {
                $order->cancel()->save();
                $this->messageManager->addSuccess(__('You canceled the order.'));
            } catch (\Magento\Framework\Model\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError(__('You have not canceled the item.'));
                $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
            }
            $this->_redirect('sales/order/view', ['order_id' => $order->getId()]);
        }
    }
}
