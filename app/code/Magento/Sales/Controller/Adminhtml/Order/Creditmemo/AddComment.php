<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order\Creditmemo;

use Magento\Backend\App\Action;
use Magento\Sales\Model\Order\Email\Sender\CreditmemoSender;

class AddComment extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader
     */
    protected $creditmemoLoader;

    /**
     * @var CreditmemoSender
     */
    protected $creditmemoSender;

    /**
     * @param Action\Context $context
     * @param \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader $creditmemoLoader
     * @param CreditmemoSender $creditmemoSender
     */
    public function __construct(
        Action\Context $context,
        \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader $creditmemoLoader,
        CreditmemoSender $creditmemoSender
    ) {
        $this->creditmemoLoader = $creditmemoLoader;
        $this->creditmemoSender = $creditmemoSender;
        parent::__construct($context);
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Sales::sales_creditmemo');
    }

    /**
     * Add comment to creditmemo history
     *
     * @return void
     */
    public function execute()
    {
        try {
            $this->getRequest()->setParam('creditmemo_id', $this->getRequest()->getParam('id'));
            $data = $this->getRequest()->getPost('comment');
            if (empty($data['comment'])) {
                throw new \Magento\Framework\Model\Exception(__('The Comment Text field cannot be empty.'));
            }
            $this->creditmemoLoader->setOrderId($this->getRequest()->getParam('order_id'));
            $this->creditmemoLoader->setCreditmemoId($this->getRequest()->getParam('creditmemo_id'));
            $this->creditmemoLoader->setCreditmemo($this->getRequest()->getParam('creditmemo'));
            $this->creditmemoLoader->setInvoiceId($this->getRequest()->getParam('invoice_id'));
            $creditmemo = $this->creditmemoLoader->load();
            $comment = $creditmemo->addComment(
                $data['comment'],
                isset($data['is_customer_notified']),
                isset($data['is_visible_on_front'])
            );
            $comment->save();

            $this->creditmemoSender->send($creditmemo, !empty($data['is_customer_notified']), $data['comment']);

            $this->_view->loadLayout();
            $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Credit Memos'));
            $response = $this->_view->getLayout()->getBlock('creditmemo_comments')->toHtml();
        } catch (\Magento\Framework\Model\Exception $e) {
            $response = ['error' => true, 'message' => $e->getMessage()];
        } catch (\Exception $e) {
            $response = ['error' => true, 'message' => __('Cannot add new comment.')];
        }
        if (is_array($response)) {
            $response = $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($response);
            $this->getResponse()->representJson($response);
        } else {
            $this->getResponse()->setBody($response);
        }
    }
}
