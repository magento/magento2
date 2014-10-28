<?php
/**
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Sales\Controller\Adminhtml\Order\Invoice;

use Magento\Backend\App\Action;
use \Magento\Framework\Model\Exception;
use \Magento\Sales\Model\Order\Email\Sender\InvoiceCommentSender;
use \Magento\Sales\Model\Order\Invoice;

class AddComment extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Sales\Controller\Adminhtml\Order\InvoiceLoader
     */
    protected $invoiceLoader;

    /**
     * @var InvoiceCommentSender
     */
    protected $invoiceCommentSender;

    /**
     * @param Action\Context $context
     * @param \Magento\Sales\Controller\Adminhtml\Order\InvoiceLoader $invoiceLoader
     * @param InvoiceCommentSender $invoiceCommentSender
     */
    public function __construct(
        Action\Context $context,
        \Magento\Sales\Controller\Adminhtml\Order\InvoiceLoader $invoiceLoader,
        InvoiceCommentSender $invoiceCommentSender
    ) {
        $this->invoiceLoader = $invoiceLoader;
        $this->invoiceCommentSender = $invoiceCommentSender;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Sales::sales_invoice');
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
            $this->_title->add(__('Invoices'));
            $orderId = $this->getRequest()->getParam('order_id');
            $invoiceId = $this->getRequest()->getParam('invoice_id');
            $invoiceData = $this->getRequest()->getParam('invoice', []);
            $invoiceData = isset($invoiceData['items']) ? $invoiceData['items'] : [];
            /** @var Invoice $invoice */
            $invoice = $this->invoiceLoader->load($orderId, $invoiceId, $invoiceData);
            $invoice->addComment(
                $data['comment'],
                isset($data['is_customer_notified']),
                isset($data['is_visible_on_front'])
            );

            $this->invoiceCommentSender->send($invoice, !empty($data['is_customer_notified']), $data['comment']);
            $invoice->save();

            $this->_view->loadLayout();
            $response = $this->_view->getLayout()->getBlock('invoice_comments')->toHtml();
        } catch (Exception $e) {
            $response = array('error' => true, 'message' => $e->getMessage());
        } catch (\Exception $e) {
            $response = array('error' => true, 'message' => __('Cannot add new comment.'));
        }
        if (is_array($response)) {
            $response = $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($response);
            $this->getResponse()->representJson($response);
        } else {
            $this->getResponse()->setBody($response);
        }
    }
}
