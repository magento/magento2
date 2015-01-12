<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order\Creditmemo;

use Magento\Backend\App\Action;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\CreditmemoSender;

class Save extends \Magento\Backend\App\Action
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
     * Save creditmemo
     * We can save only new creditmemo. Existing creditmemos are not editable
     *
     * @return void
     */
    public function execute()
    {
        $data = $this->getRequest()->getPost('creditmemo');
        if (!empty($data['comment_text'])) {
            $this->_getSession()->setCommentText($data['comment_text']);
        }
        try {
            $this->creditmemoLoader->setOrderId($this->getRequest()->getParam('order_id'));
            $this->creditmemoLoader->setCreditmemoId($this->getRequest()->getParam('creditmemo_id'));
            $this->creditmemoLoader->setCreditmemo($this->getRequest()->getParam('creditmemo'));
            $this->creditmemoLoader->setInvoiceId($this->getRequest()->getParam('invoice_id'));
            $creditmemo = $this->creditmemoLoader->load();
            if ($creditmemo) {
                if (!$creditmemo->isValidGrandTotal()) {
                    throw new \Magento\Framework\Model\Exception(__('Credit memo\'s total must be positive.'));
                }

                $comment = '';
                if (!empty($data['comment_text'])) {
                    $creditmemo->addComment(
                        $data['comment_text'],
                        isset($data['comment_customer_notify']),
                        isset($data['is_visible_on_front'])
                    );
                    if (isset($data['comment_customer_notify'])) {
                        $comment = $data['comment_text'];
                    }
                }

                if (isset($data['do_refund'])) {
                    $creditmemo->setRefundRequested(true);
                }
                if (isset($data['do_offline'])) {
                    //do not allow online refund for Refund to Store Credit
                    if (!$data['do_offline'] && !empty($data['refund_customerbalance_return_enable'])) {
                        throw new \Magento\Framework\Model\Exception(
                            __('Cannot create online refund for Refund to Store Credit.')
                        );
                    }
                    $creditmemo->setOfflineRequested((bool)(int)$data['do_offline']);
                }

                $creditmemo->register();
                if (!empty($data['send_email'])) {
                    $creditmemo->setEmailSent(true);
                }

                $creditmemo->getOrder()->setCustomerNoteNotify(!empty($data['send_email']));
                $transactionSave = $this->_objectManager->create(
                    'Magento\Framework\DB\Transaction'
                )->addObject(
                    $creditmemo
                )->addObject(
                    $creditmemo->getOrder()
                );
                if ($creditmemo->getInvoice()) {
                    $transactionSave->addObject($creditmemo->getInvoice());
                }
                $transactionSave->save();
                $this->creditmemoSender->send($creditmemo, !empty($data['send_email']), $comment);

                $this->messageManager->addSuccess(__('You created the credit memo.'));
                $this->_getSession()->getCommentText(true);
                $this->_redirect('sales/order/view', ['order_id' => $creditmemo->getOrderId()]);
                return;
            } else {
                $this->_forward('noroute');
                return;
            }
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            $this->_getSession()->setFormData($data);
        } catch (\Exception $e) {
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            $this->messageManager->addError(__('Cannot save the credit memo.'));
        }
        $this->_redirect('sales/*/new', ['_current' => true]);
    }
}
