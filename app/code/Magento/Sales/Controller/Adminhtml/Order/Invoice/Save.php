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

use \Magento\Framework\Model\Exception;
use Magento\Backend\App\Action;
use \Magento\Sales\Model\Order\Email\Sender\InvoiceCommentSender;
use \Magento\Sales\Model\Order\Email\Sender\ShipmentSender;
use \Magento\Sales\Model\Order\Invoice;

class Save extends \Magento\Backend\App\Action
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
     * @var ShipmentSender
     */
    protected $shipmentSender;

    /**
     * @param Action\Context $context
     * @param \Magento\Sales\Controller\Adminhtml\Order\InvoiceLoader $invoiceLoader
     * @param InvoiceCommentSender $invoiceCommentSender
     * @param ShipmentSender $shipmentSender
     */
    public function __construct(
        Action\Context $context,
        \Magento\Sales\Controller\Adminhtml\Order\InvoiceLoader $invoiceLoader,
        InvoiceCommentSender $invoiceCommentSender,
        ShipmentSender $shipmentSender
    ) {
        $this->invoiceLoader = $invoiceLoader;
        $this->invoiceCommentSender = $invoiceCommentSender;
        $this->shipmentSender = $shipmentSender;
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
     * Prepare shipment
     *
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @return \Magento\Sales\Model\Order\Shipment|false
     */
    protected function _prepareShipment($invoice)
    {
        $savedQtys = array();
        $data = $this->getRequest()->getParam('invoice');
        if (isset($data['items'])) {
            $savedQtys = $data['items'];
        }
        $shipment = $this->_objectManager->create(
            'Magento\Sales\Model\Service\Order',
            array('order' => $invoice->getOrder())
        )->prepareShipment(
            $savedQtys
        );
        if (!$shipment->getTotalQty()) {
            return false;
        }

        $shipment->register();
        $tracks = $this->getRequest()->getPost('tracking');
        if ($tracks) {
            foreach ($tracks as $data) {
                $track = $this->_objectManager->create('Magento\Sales\Model\Order\Shipment\Track')->addData($data);
                $shipment->addTrack($track);
            }
        }
        return $shipment;
    }

    /**
     * Save invoice
     * We can save only new invoice. Existing invoices are not editable
     *
     * @return void
     */
    public function execute()
    {
        $data = $this->getRequest()->getPost('invoice');
        $orderId = $this->getRequest()->getParam('order_id');

        if (!empty($data['comment_text'])) {
            $this->_objectManager->get('Magento\Backend\Model\Session')->setCommentText($data['comment_text']);
        }

        try {
            $invoiceId = $this->getRequest()->getParam('invoice_id');
            $invoiceData = $this->getRequest()->getParam('invoice', []);
            $invoiceData = isset($invoiceData['items']) ? $invoiceData['items'] : [];
            /** @var Invoice $invoice */
            $invoice = $this->invoiceLoader->load($orderId, $invoiceId, $invoiceData);
            if ($invoice) {

                if (!empty($data['capture_case'])) {
                    $invoice->setRequestedCaptureCase($data['capture_case']);
                }

                if (!empty($data['comment_text'])) {
                    $invoice->addComment(
                        $data['comment_text'],
                        isset($data['comment_customer_notify']),
                        isset($data['is_visible_on_front'])
                    );
                }

                $invoice->register();

                if (!empty($data['send_email'])) {
                    $invoice->setEmailSent(true);
                }

                $invoice->getOrder()->setCustomerNoteNotify(!empty($data['send_email']));
                $invoice->getOrder()->setIsInProcess(true);

                $transactionSave = $this->_objectManager->create(
                    'Magento\Framework\DB\Transaction'
                )->addObject(
                    $invoice
                )->addObject(
                    $invoice->getOrder()
                );
                $shipment = false;
                if (!empty($data['do_shipment']) || (int)$invoice->getOrder()->getForcedShipmentWithInvoice()) {
                    $shipment = $this->_prepareShipment($invoice);
                    if ($shipment) {
                        $shipment->setEmailSent($invoice->getEmailSent());
                        $transactionSave->addObject($shipment);
                    }
                }
                $transactionSave->save();

                if (isset($shippingResponse) && $shippingResponse->hasErrors()) {
                    $this->messageManager->addError(
                        __(
                            'The invoice and the shipment  have been created. ' .
                            'The shipping label cannot be created now.'
                        )
                    );
                } elseif (!empty($data['do_shipment'])) {
                    $this->messageManager->addSuccess(__('You created the invoice and shipment.'));
                } else {
                    $this->messageManager->addSuccess(__('The invoice has been created.'));
                }

                // send invoice/shipment emails
                $comment = '';
                if (isset($data['comment_customer_notify'])) {
                    $comment = $data['comment_text'];
                }
                try {
                    $this->invoiceCommentSender->send($invoice, !empty($data['send_email']), $comment);
                } catch (\Exception $e) {
                    $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
                    $this->messageManager->addError(__('We can\'t send the invoice email.'));
                }
                if ($shipment) {
                    try {
                        $this->shipmentSender->send($shipment, !empty($data['send_email']));
                    } catch (\Exception $e) {
                        $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
                        $this->messageManager->addError(__('We can\'t send the shipment.'));
                    }
                }
                $this->_objectManager->get('Magento\Backend\Model\Session')->getCommentText(true);
                $this->_redirect('sales/order/view', array('order_id' => $orderId));
            } else {
                $this->_redirect('sales/*/new', array('order_id' => $orderId));
            }
            return;
        } catch (Exception $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(__('We can\'t save the invoice.'));
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
        }
        $this->_redirect('sales/*/new', array('order_id' => $orderId));
    }
}
