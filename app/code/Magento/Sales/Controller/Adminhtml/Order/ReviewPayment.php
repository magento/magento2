<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Sales\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;

class ReviewPayment extends \Magento\Sales\Controller\Adminhtml\Order
{
    /**
     * Manage payment state
     *
     * Either denies or approves a payment that is in "review" state
     *
     * @return void
     */
    public function execute()
    {
        try {
            $order = $this->_initOrder();
            if (!$order) {
                return;
            }
            $action = $this->getRequest()->getParam('action', '');
            switch ($action) {
                case 'accept':
                    $order->getPayment()->accept();
                    $message = __('The payment has been accepted.');
                    break;
                case 'deny':
                    $order->getPayment()->deny();
                    $message = __('The payment has been denied.');
                    break;
                case 'update':
                    $order->getPayment()->registerPaymentReviewAction(
                        \Magento\Sales\Model\Order\Payment::REVIEW_ACTION_UPDATE,
                        true
                    );
                    $message = __('The payment update has been made.');
                    break;
                default:
                    throw new \Exception(sprintf('Action "%s" is not supported.', $action));
            }
            $order->save();
            $this->messageManager->addSuccess($message);
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(__('We couldn\'t update the payment.'));
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
        }
        $this->_redirect('sales/order/view', ['order_id' => $order->getId()]);
    }
}
