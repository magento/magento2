<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order\Invoice;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Model\Exception;
use Magento\Sales\Model\Order\Email\Sender\InvoiceCommentSender;
use Magento\Sales\Model\Order\Invoice;
use Magento\Backend\App\Action;
use Magento\Framework\Registry;
use Magento\Framework\Controller\Result\JSONFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\RawFactory;

class AddComment extends \Magento\Sales\Controller\Adminhtml\Invoice\AbstractInvoice\View
{
    /**
     * @var InvoiceCommentSender
     */
    protected $invoiceCommentSender;

    /**
     * @var JSONFactory
     */
    protected $resultJsonFactory;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    /**
     * @param Action\Context $context
     * @param Registry $registry
     * @param InvoiceCommentSender $invoiceCommentSender
     * @param JSONFactory $resultJsonFactory
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     * @param PageFactory $resultPageFactory
     * @param RawFactory $resultRawFactory
     */
    public function __construct(
        Context $context,
        Registry $registry,
        InvoiceCommentSender $invoiceCommentSender,
        JSONFactory $resultJsonFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,,
        PageFactory $resultPageFactory,
        RawFactory $resultRawFactory
    ) {
        $this->invoiceCommentSender = $invoiceCommentSender;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultRawFactory = $resultRawFactory;
        parent::__construct($context, $registry, $resultForwardFactory);
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
                /** @var \Magento\Framework\Controller\Result\Forward $resultForward */
                $resultForward = $this->resultForwardFactory->create();
                return $resultForward->forward('noroute');
            }
            $invoice->addComment(
                $data['comment'],
                isset($data['is_customer_notified']),
                isset($data['is_visible_on_front'])
            );

            $this->invoiceCommentSender->send($invoice, !empty($data['is_customer_notified']), $data['comment']);
            $invoice->save();

            /** @var \Magento\Framework\View\Result\Page $resultPage */
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->prepend(__('Invoices'));
            $response = $resultPage->getLayout()->getBlock('invoice_comments')->toHtml();
        } catch (Exception $e) {
            $response = ['error' => true, 'message' => $e->getMessage()];
        } catch (\Exception $e) {
            $response = ['error' => true, 'message' => __('Cannot add new comment.')];
        }
        if (is_array($response)) {
            $response = $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($response);
            /** @var \Magento\Framework\Controller\Result\JSON $resultJson */
            $resultJson = $this->resultJsonFactory->create();
            $resultJson->setData($response);
            return $resultJson;
        } else {
            /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
            $resultRaw = $this->resultRawFactory->create();
            $resultRaw->setContents($response);
            return $resultRaw;
        }
    }
}
