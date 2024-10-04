<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Email\Sender\OrderCommentSender;

/**
 * Class AddComment
 *
 * Controller responsible for addition of the order comment to the order
 */
class AddComment extends \Magento\Sales\Controller\Adminhtml\Order implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_Sales::comment';

    /**
     * ACL resource needed to send comment email notification
     */
    public const ADMIN_SALES_EMAIL_RESOURCE = 'Magento_Sales::emails';

    /**
     * Add order comment action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $order = $this->_initOrder();
        if ($order) {
            try {
                $data = $this->getRequest()->getPost('history');
                if (empty($data['comment']) && $data['status'] == $order->getDataByKey('status')) {
                    $error = 'Please provide a comment text or ' .
                        'update the order status to be able to submit a comment for this order.';
                    throw new \Magento\Framework\Exception\LocalizedException(__($error));
                }

                $orderStatus = $this->getOrderStatus($order, $data['status']);
                $order->setStatus($orderStatus);
                $notify = $data['is_customer_notified'] ?? false;
                $visible = $data['is_visible_on_front'] ?? false;

                if ($notify && !$this->_authorization->isAllowed(self::ADMIN_SALES_EMAIL_RESOURCE)) {
                    $notify = false;
                }

                $comment = trim(strip_tags($data['comment']));
                $history = $order->addStatusHistoryComment($comment, $orderStatus);
                $history->setIsVisibleOnFront($visible);
                $history->setIsCustomerNotified($notify);
                $history->save();

                $order->save();
                /** @var OrderCommentSender $orderCommentSender */
                $orderCommentSender = $this->_objectManager
                    ->create(\Magento\Sales\Model\Order\Email\Sender\OrderCommentSender::class);

                $orderCommentSender->send($order, $notify, $comment);

                return $this->resultPageFactory->create();
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $response = ['error' => true, 'message' => $e->getMessage()];
            } catch (\Exception $e) {
                $response = ['error' => true, 'message' => __('We cannot add order history.')];
            }
            if (is_array($response)) {
                $resultJson = $this->resultJsonFactory->create();
                $resultJson->setData($response);
                return $resultJson;
            }
        }
        return $this->resultRedirectFactory->create()->setPath('sales/*/');
    }

    /**
     * Get order status to set
     *
     * @param OrderInterface $order
     * @param string $historyStatus
     * @return string
     */
    private function getOrderStatus(OrderInterface $order, string $historyStatus): string
    {
        $config = $order->getConfig();
        if ($config === null) {
            return $historyStatus;
        }
        $statuses = $config->getStateStatuses($order->getState());

        if (!isset($statuses[$historyStatus])) {
            return $order->getDataByKey('status');
        }
        return $historyStatus;
    }
}
