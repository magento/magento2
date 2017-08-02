<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;
use Magento\Sales\Model\Order\Email\Sender\OrderCommentSender;

/**
 * Class \Magento\Sales\Controller\Adminhtml\Order\AddComment
 *
 * @since 2.0.0
 */
class AddComment extends \Magento\Sales\Controller\Adminhtml\Order
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Sales::comment';

    /**
     * Add order comment action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @since 2.0.0
     */
    public function execute()
    {
        $order = $this->_initOrder();
        if ($order) {
            try {
                $data = $this->getRequest()->getPost('history');
                if (empty($data['comment']) && $data['status'] == $order->getDataByKey('status')) {
                    throw new \Magento\Framework\Exception\LocalizedException(__('Please enter a comment.'));
                }

                $notify = isset($data['is_customer_notified']) ? $data['is_customer_notified'] : false;
                $visible = isset($data['is_visible_on_front']) ? $data['is_visible_on_front'] : false;

                $history = $order->addStatusHistoryComment($data['comment'], $data['status']);
                $history->setIsVisibleOnFront($visible);
                $history->setIsCustomerNotified($notify);
                $history->save();

                $comment = trim(strip_tags($data['comment']));

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
}
