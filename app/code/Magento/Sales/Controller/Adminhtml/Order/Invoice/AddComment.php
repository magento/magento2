<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order\Invoice;

use Magento\Framework\Model\Exception;
use Magento\Sales\Model\Order\Email\Sender\InvoiceCommentSender;
use Magento\Sales\Model\Order\Invoice;
use Magento\Backend\App\Action;
use Magento\Framework\Registry;

class AddComment extends \Magento\Sales\Controller\Adminhtml\Invoice\AbstractInvoice\View
{
    /**
     * @var InvoiceCommentSender
     */
    protected $invoiceCommentSender;

    /**
     * @param Action\Context $context
     * @param Registry $registry
     * @param InvoiceCommentSender $invoiceCommentSender
     */
    public function __construct(
        Action\Context $context,
        Registry $registry,
        InvoiceCommentSender $invoiceCommentSender
    ) {
        $this->invoiceCommentSender = $invoiceCommentSender;
        parent::__construct($context, $registry);
    }

    /**
     * Add comment to invoice action
     *
     * @return void
     */
    public function execute()
    {
        try {
            $this->getRequest()->setParam('invoice_id', $this->getRequest()->getParam('id'));
            $data = $this->getRequest()->getPost('comment');
            if (empty($data['comment'])) {
                throw new Exception(__('The Comment Text field cannot be empty.'));
            }
            $invoice = $this->getInvoice();
            if (!$invoice) {
                $this->_forward('noroute');
                return;
            }
            $invoice->addComment(
                $data['comment'],
                isset($data['is_customer_notified']),
                isset($data['is_visible_on_front'])
            );

            $this->invoiceCommentSender->send($invoice, !empty($data['is_customer_notified']), $data['comment']);
            $invoice->save();

            $this->_view->loadLayout();
            $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Invoices'));
            $response = $this->_view->getLayout()->getBlock('invoice_comments')->toHtml();
        } catch (Exception $e) {
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
