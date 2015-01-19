<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order\Invoice;

use Magento\Backend\App\Action;
use Magento\Framework\Model\Exception;
use Magento\Framework\Registry;
use Magento\Sales\Model\Order\Email\Sender\InvoiceCommentSender;
use Magento\Sales\Model\Order\Email\Sender\ShipmentSender;
use Magento\Sales\Model\Order\Invoice;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var InvoiceCommentSender
     */
    protected $invoiceCommentSender;

    /**
     * @var ShipmentSender
     */
    protected $shipmentSender;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @param Action\Context $context
     * @param Registry $registry
     * @param InvoiceCommentSender $invoiceCommentSender
     * @param ShipmentSender $shipmentSender
     */
    public function __construct(
        Action\Context $context,
        Registry $registry,
        InvoiceCommentSender $invoiceCommentSender,
        ShipmentSender $shipmentSender
    ) {
        $this->registry = $registry;
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
        $savedQtys = [];
        $data = $this->getRequest()->getParam('invoice');
        if (isset($data['items'])) {
            $savedQtys = $data['items'];
        }
        $shipment = $this->_objectManager->create(
            'Magento\Sales\Model\Service\Order',
            ['order' => $invoice->getOrder()]
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
            $invoiceData = $this->getRequest()->getParam('invoice', []);
            $invoiceItems = isset($invoiceData['items']) ? $invoiceData['items'] : [];
            /** @var \Magento\Sales\Model\Order $order */
            $order = $this->_objectManager->create('Magento\Sales\Model\Order')->load($orderId);
            if (!$order->getId()) {
                throw new \Magento\Framework\Exception(__('The order no longer exists.'));
            }

            if (!$order->canInvoice()) {
                throw new \Magento\Framework\Exception(__('The order does not allow an invoice to be created.'));
            }

            /** @var \Magento\Sales\Model\Order\Invoice $invoice */
            $invoice = $this->_objectManager->create('Magento\Sales\Model\Service\Order', ['order' => $order])
                ->prepareInvoice($invoiceItems);

            if (!$invoice) {
                throw new Exception(__('We can\'t save the invoice.'));
            }

            if (!$invoice->getTotalQty()) {
                throw new \Magento\Framework\Exception(__('Cannot create an invoice without products.'));
            }
            $this->registry->register('current_invoice', $invoice);
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
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                $this->messageManager->addError(__('We can\'t send the invoice email.'));
            }
            if ($shipment) {
                try {
                    $this->shipmentSender->send($shipment, !empty($data['send_email']));
                } catch (\Exception $e) {
                    $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                    $this->messageManager->addError(__('We can\'t send the shipment.'));
                }
            }
            $this->_objectManager->get('Magento\Backend\Model\Session')->getCommentText(true);
            $this->_redirect('sales/order/view', ['order_id' => $orderId]);
            return;
        } catch (Exception $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(__('We can\'t save the invoice.'));
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
        }
        $this->_redirect('sales/*/new', ['order_id' => $orderId]);
    }
}
