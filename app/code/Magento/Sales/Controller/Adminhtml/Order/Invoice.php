<?php
/**
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
namespace Magento\Sales\Controller\Adminhtml\Order;

use Magento\Framework\Model\Exception;
use Magento\Framework\App\ResponseInterface;

/**
 * Adminhtml sales order invoice edit controller
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Invoice extends \Magento\Sales\Controller\Adminhtml\Invoice\AbstractInvoice
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\App\Action\Title
     */
    protected $_title;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context, $fileFactory);
    }

    /**
     * Get requested items qty's from request
     *
     * @return array
     */
    protected function _getItemQtys()
    {
        $data = $this->getRequest()->getParam('invoice');
        if (isset($data['items'])) {
            $qtys = $data['items'];
        } else {
            $qtys = array();
        }
        return $qtys;
    }

    /**
     * Initialize invoice model instance
     *
     * @return \Magento\Sales\Model\Order\Invoice
     * @throws \Magento\Framework\Model\Exception
     */
    protected function _initInvoice()
    {
        $this->_title->add(__('Invoices'));

        $invoice = false;
        $invoiceId = $this->getRequest()->getParam('invoice_id');
        $orderId = $this->getRequest()->getParam('order_id');
        if ($invoiceId) {
            $invoice = $this->_objectManager->create('Magento\Sales\Model\Order\Invoice')->load($invoiceId);
            if (!$invoice->getId()) {
                $this->messageManager->addError(__('The invoice no longer exists.'));
                return false;
            }
        } elseif ($orderId) {
            $order = $this->_objectManager->create('Magento\Sales\Model\Order')->load($orderId);
            /**
             * Check order existing
             */
            if (!$order->getId()) {
                $this->messageManager->addError(__('The order no longer exists.'));
                return false;
            }
            /**
             * Check invoice create availability
             */
            if (!$order->canInvoice()) {
                $this->messageManager->addError(__('The order does not allow an invoice to be created.'));
                return false;
            }
            $savedQtys = $this->_getItemQtys();
            $invoice = $this->_objectManager->create(
                'Magento\Sales\Model\Service\Order',
                array('order' => $order)
            )->prepareInvoice(
                $savedQtys
            );
            if (!$invoice->getTotalQty()) {
                throw new Exception(__('Cannot create an invoice without products.'));
            }
        }

        $this->_coreRegistry->register('current_invoice', $invoice);
        return $invoice;
    }

    /**
     * Save data for invoice and related order
     *
     * @param   \Magento\Sales\Model\Order\Invoice $invoice
     * @return  $this
     */
    protected function _saveInvoice($invoice)
    {
        $invoice->getOrder()->setIsInProcess(true);
        $this->_objectManager->create(
            'Magento\Framework\DB\Transaction'
        )->addObject(
            $invoice
        )->addObject(
            $invoice->getOrder()
        )->save();

        return $this;
    }

    /**
     * Prepare shipment
     *
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @return \Magento\Sales\Model\Order\Shipment|false
     */
    protected function _prepareShipment($invoice)
    {
        $savedQtys = $this->_getItemQtys();
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
     * Invoice information page
     *
     * @return void
     */
    public function viewAction()
    {
        $invoice = $this->_initInvoice();
        if ($invoice) {
            $this->_title->add(sprintf("#%s", $invoice->getIncrementId()));

            $this->_view->loadLayout();
            $this->_setActiveMenu('Magento_Sales::sales_order');
            $this->_view->getLayout()->getBlock(
                'sales_invoice_view'
            )->updateBackButtonUrl(
                $this->getRequest()->getParam('come_from')
            );
            $this->_view->renderLayout();
        } else {
            $this->_forward('noroute');
        }
    }

    /**
     * Start create invoice action
     *
     * @return void
     */
    public function startAction()
    {
        /**
         * Clear old values for invoice qty's
         */
        $this->_getSession()->getInvoiceItemQtys(true);
        $this->_redirect('sales/*/new', array('order_id' => $this->getRequest()->getParam('order_id')));
    }

    /**
     * Invoice create page
     *
     * @return void
     */
    public function newAction()
    {
        $invoice = $this->_initInvoice();
        if ($invoice) {
            $this->_title->add(__('New Invoice'));

            $comment = $this->_objectManager->get('Magento\Backend\Model\Session')->getCommentText(true);
            if ($comment) {
                $invoice->setCommentText($comment);
            }

            $this->_view->loadLayout();
            $this->_setActiveMenu('Magento_Sales::sales_order');
            $this->_view->renderLayout();
        } else {
            $this->_redirect('sales/order/view', array('order_id' => $this->getRequest()->getParam('order_id')));
        }
    }

    /**
     * Update items qty action
     *
     * @return void
     */
    public function updateQtyAction()
    {
        try {
            $invoice = $this->_initInvoice(true);
            // Save invoice comment text in current invoice object in order to display it in corresponding view
            $invoiceRawData = $this->getRequest()->getParam('invoice');
            $invoiceRawCommentText = $invoiceRawData['comment_text'];
            $invoice->setCommentText($invoiceRawCommentText);

            $this->_view->loadLayout();
            $response = $this->_view->getLayout()->getBlock('order_items')->toHtml();
        } catch (Exception $e) {
            $response = array('error' => true, 'message' => $e->getMessage());
        } catch (\Exception $e) {
            $response = array('error' => true, 'message' => __('Cannot update item quantity.'));
        }
        if (is_array($response)) {
            $this->getResponse()->representJson(
                $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($response)
            );
        } else {
            $this->getResponse()->setBody($response);
        }
    }

    /**
     * Save invoice
     * We can save only new invoice. Existing invoices are not editable
     *
     * @return void
     */
    public function saveAction()
    {
        $data = $this->getRequest()->getPost('invoice');
        $orderId = $this->getRequest()->getParam('order_id');

        if (!empty($data['comment_text'])) {
            $this->_objectManager->get('Magento\Backend\Model\Session')->setCommentText($data['comment_text']);
        }

        try {
            $invoice = $this->_initInvoice();
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
                    $invoice->sendEmail(!empty($data['send_email']), $comment);
                } catch (\Exception $e) {
                    $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
                    $this->messageManager->addError(__('We can\'t send the invoice email.'));
                }
                if ($shipment) {
                    try {
                        $shipment->sendEmail(!empty($data['send_email']));
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

    /**
     * Capture invoice action
     *
     * @return void
     */
    public function captureAction()
    {
        $invoice = $this->_initInvoice();
        if ($invoice) {
            try {
                $invoice->capture();
                $this->_saveInvoice($invoice);
                $this->messageManager->addSuccess(__('The invoice has been captured.'));
            } catch (Exception $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError(__('Invoice capturing error'));
            }
            $this->_redirect('sales/*/view', array('invoice_id' => $invoice->getId()));
        } else {
            $this->_forward('noroute');
        }
    }

    /**
     * Cancel invoice action
     *
     * @return void
     */
    public function cancelAction()
    {
        $invoice = $this->_initInvoice();
        if ($invoice) {
            try {
                $invoice->cancel();
                $this->_saveInvoice($invoice);
                $this->messageManager->addSuccess(__('You canceled the invoice.'));
            } catch (Exception $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError(__('Invoice canceling error'));
            }
            $this->_redirect('sales/*/view', array('invoice_id' => $invoice->getId()));
        } else {
            $this->_forward('noroute');
        }
    }

    /**
     * Void invoice action
     *
     * @return void
     */
    public function voidAction()
    {
        $invoice = $this->_initInvoice();
        if ($invoice) {
            try {
                $invoice->void();
                $this->_saveInvoice($invoice);
                $this->messageManager->addSuccess(__('The invoice has been voided.'));
            } catch (Exception $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError(__('Invoice voiding error'));
            }
            $this->_redirect('sales/*/view', array('invoice_id' => $invoice->getId()));
        } else {
            $this->_forward('noroute');
        }
    }

    /**
     * Add comment to invoice action
     *
     * @return void
     */
    public function addCommentAction()
    {
        try {
            $this->getRequest()->setParam('invoice_id', $this->getRequest()->getParam('id'));
            $data = $this->getRequest()->getPost('comment');
            if (empty($data['comment'])) {
                throw new Exception(__('The Comment Text field cannot be empty.'));
            }
            $invoice = $this->_initInvoice();
            $invoice->addComment(
                $data['comment'],
                isset($data['is_customer_notified']),
                isset($data['is_visible_on_front'])
            );
            $invoice->sendUpdateEmail(!empty($data['is_customer_notified']), $data['comment']);
            $invoice->save();

            $this->_view->loadLayout();
            $response = $this->_view->getLayout()->getBlock('invoice_comments')->toHtml();
        } catch (Exception $e) {
            $response = array('error' => true, 'message' => $e->getMessage());
        } catch (\Exception $e) {
            $response = array('error' => true, 'message' => __('Cannot add new comment.'));
        }
        if (is_array($response)) {
            $this->getResponse()->representJson(
                $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($response)
            );
        } else {
            $this->getResponse()->setBody($response);
        }
    }

    /**
     * Create pdf for current invoice
     *
     * @return ResponseInterface|void
     */
    public function printAction()
    {
        $this->_initInvoice();
        parent::printAction();
    }
}
