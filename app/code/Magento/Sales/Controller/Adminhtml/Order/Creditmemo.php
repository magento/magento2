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

use Magento\Sales\Model\Order;
use Magento\Framework\App\ResponseInterface;

/**
 * Adminhtml sales order creditmemo controller
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Creditmemo extends \Magento\Sales\Controller\Adminhtml\Creditmemo\AbstractCreditmemo
{
    /**
     * Get requested items qtys and return to stock flags
     *
     * @return array
     */
    protected function _getItemData()
    {
        $data = $this->getRequest()->getParam('creditmemo');
        if (!$data) {
            $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getFormData(true);
        }

        if (isset($data['items'])) {
            $qtys = $data['items'];
        } else {
            $qtys = array();
        }
        return $qtys;
    }

    /**
     * Check if creditmeno can be created for order
     * @param Order $order
     * @return bool
     */
    protected function _canCreditmemo($order)
    {
        /**
         * Check order existing
         */
        if (!$order->getId()) {
            $this->messageManager->addError(__('The order no longer exists.'));
            return false;
        }

        /**
         * Check creditmemo create availability
         */
        if (!$order->canCreditmemo()) {
            $this->messageManager->addError(__('Cannot create credit memo for the order.'));
            return false;
        }
        return true;
    }

    /**
     * Initialize requested invoice instance
     *
     * @param Order $order
     * @return bool
     */
    protected function _initInvoice($order)
    {
        $invoiceId = $this->getRequest()->getParam('invoice_id');
        if ($invoiceId) {
            $invoice = $this->_objectManager->create(
                'Magento\Sales\Model\Order\Invoice'
            )->load(
                $invoiceId
            )->setOrder(
                $order
            );
            if ($invoice->getId()) {
                return $invoice;
            }
        }
        return false;
    }

    /**
     * Initialize creditmemo model instance
     *
     * @param bool $update
     * @return \Magento\Sales\Model\Order\Creditmemo|false
     */
    protected function _initCreditmemo($update = false)
    {
        $this->_title->add(__('Credit Memos'));

        $creditmemo = false;
        $creditmemoId = $this->getRequest()->getParam('creditmemo_id');
        $orderId = $this->getRequest()->getParam('order_id');
        if ($creditmemoId) {
            $creditmemo = $this->_objectManager->create('Magento\Sales\Model\Order\Creditmemo')->load($creditmemoId);
        } elseif ($orderId) {
            $data = $this->getRequest()->getParam('creditmemo');
            $order = $this->_objectManager->create('Magento\Sales\Model\Order')->load($orderId);
            $invoice = $this->_initInvoice($order);

            if (!$this->_canCreditmemo($order)) {
                return false;
            }

            $savedData = $this->_getItemData();

            $qtys = array();
            $backToStock = array();
            foreach ($savedData as $orderItemId => $itemData) {
                if (isset($itemData['qty'])) {
                    $qtys[$orderItemId] = $itemData['qty'];
                }
                if (isset($itemData['back_to_stock'])) {
                    $backToStock[$orderItemId] = true;
                }
            }
            $data['qtys'] = $qtys;

            $service = $this->_objectManager->create('Magento\Sales\Model\Service\Order', array('order' => $order));
            if ($invoice) {
                $creditmemo = $service->prepareInvoiceCreditmemo($invoice, $data);
            } else {
                $creditmemo = $service->prepareCreditmemo($data);
            }

            /**
             * Process back to stock flags
             */
            foreach ($creditmemo->getAllItems() as $creditmemoItem) {
                $orderItem = $creditmemoItem->getOrderItem();
                $parentId = $orderItem->getParentItemId();
                if (isset($backToStock[$orderItem->getId()])) {
                    $creditmemoItem->setBackToStock(true);
                } elseif ($orderItem->getParentItem() && isset($backToStock[$parentId]) && $backToStock[$parentId]) {
                    $creditmemoItem->setBackToStock(true);
                } elseif (empty($savedData)) {
                    $creditmemoItem->setBackToStock(
                        $this->_objectManager->get('Magento\CatalogInventory\Helper\Data')->isAutoReturnEnabled()
                    );
                } else {
                    $creditmemoItem->setBackToStock(false);
                }
            }
        }

        $this->_eventManager->dispatch(
            'adminhtml_sales_order_creditmemo_register_before',
            array('creditmemo' => $creditmemo, 'request' => $this->getRequest())
        );

        $this->_objectManager->get('Magento\Framework\Registry')->register('current_creditmemo', $creditmemo);
        return $creditmemo;
    }

    /**
     * Save creditmemo and related order, invoice in one transaction
     *
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @return $this
     */
    protected function _saveCreditmemo($creditmemo)
    {
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

        return $this;
    }

    /**
     * Creditmemo information page
     *
     * @return void
     */
    public function viewAction()
    {
        $creditmemo = $this->_initCreditmemo();
        if ($creditmemo) {
            if ($creditmemo->getInvoice()) {
                $this->_title->add(__("View Memo for #%1", $creditmemo->getInvoice()->getIncrementId()));
            } else {
                $this->_title->add(__("View Memo"));
            }

            $this->_view->loadLayout();
            $this->_view->getLayout()->getBlock(
                'sales_creditmemo_view'
            )->updateBackButtonUrl(
                $this->getRequest()->getParam('come_from')
            );
            $this->_setActiveMenu('Magento_Sales::sales_creditmemo');
            $this->_view->renderLayout();
        } else {
            $this->_forward('noroute');
        }
    }

    /**
     * Start create creditmemo action
     *
     * @return void
     */
    public function startAction()
    {
        /**
         * Clear old values for creditmemo qty's
         */
        $this->_redirect('sales/*/new', array('_current' => true));
    }

    /**
     * Creditmemo create page
     *
     * @return void
     */
    public function newAction()
    {
        if ($creditmemo = $this->_initCreditmemo()) {
            if ($creditmemo->getInvoice()) {
                $this->_title->add(__("New Memo for #%1", $creditmemo->getInvoice()->getIncrementId()));
            } else {
                $this->_title->add(__("New Memo"));
            }

            if ($comment = $this->_objectManager->get('Magento\Backend\Model\Session')->getCommentText(true)) {
                $creditmemo->setCommentText($comment);
            }

            $this->_view->loadLayout();
            $this->_setActiveMenu('Magento_Sales::sales_order');
            $this->_view->renderLayout();
        } else {
            $this->_forward('noroute');
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
            $this->_initCreditmemo(true);
            $this->_view->loadLayout();
            $response = $this->_view->getLayout()->getBlock('order_items')->toHtml();
        } catch (\Magento\Framework\Model\Exception $e) {
            $response = array('error' => true, 'message' => $e->getMessage());
        } catch (\Exception $e) {
            $response = array('error' => true, 'message' => __('Cannot update the item\'s quantity.'));
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
     * Save creditmemo
     * We can save only new creditmemo. Existing creditmemos are not editable
     *
     * @return void
     */
    public function saveAction()
    {
        $data = $this->getRequest()->getPost('creditmemo');
        if (!empty($data['comment_text'])) {
            $this->_getSession()->setCommentText($data['comment_text']);
        }
        try {
            $creditmemo = $this->_initCreditmemo();
            if ($creditmemo) {
                if ($creditmemo->getGrandTotal() <= 0 && !$creditmemo->getAllowZeroGrandTotal()) {
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
                $this->_saveCreditmemo($creditmemo);
                $creditmemo->sendEmail(!empty($data['send_email']), $comment);
                $this->messageManager->addSuccess(__('You created the credit memo.'));
                $this->_getSession()->getCommentText(true);
                $this->_redirect('sales/order/view', array('order_id' => $creditmemo->getOrderId()));
                return;
            } else {
                $this->_forward('noroute');
                return;
            }
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            $this->_getSession()->setFormData($data);
        } catch (\Exception $e) {
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
            $this->messageManager->addError(__('Cannot save the credit memo.'));
        }
        $this->_redirect('sales/*/new', array('_current' => true));
    }

    /**
     * Cancel creditmemo action
     *
     * @return void
     */
    public function cancelAction()
    {
        $creditmemo = $this->_initCreditmemo();
        if ($creditmemo) {
            try {
                $creditmemo->cancel();
                $this->_saveCreditmemo($creditmemo);
                $this->messageManager->addSuccess(__('The credit memo has been canceled.'));
            } catch (\Magento\Framework\Model\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError(__('You canceled the credit memo.'));
            }
            $this->_redirect('sales/*/view', array('creditmemo_id' => $creditmemo->getId()));
        } else {
            $this->_forward('noroute');
        }
    }

    /**
     * Void creditmemo action
     *
     * @return void
     */
    public function voidAction()
    {
        $creditmemo = $this->_initCreditmemo();
        if ($creditmemo) {
            try {
                $creditmemo->void();
                $this->_saveCreditmemo($creditmemo);
                $this->messageManager->addSuccess(__('You voided the credit memo.'));
            } catch (\Magento\Framework\Model\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError(__('We can\'t void the credit memo.'));
            }
            $this->_redirect('sales/*/view', array('creditmemo_id' => $creditmemo->getId()));
        } else {
            $this->_forward('noroute');
        }
    }

    /**
     * Add comment to creditmemo history
     *
     * @return void
     */
    public function addCommentAction()
    {
        try {
            $this->getRequest()->setParam('creditmemo_id', $this->getRequest()->getParam('id'));
            $data = $this->getRequest()->getPost('comment');
            if (empty($data['comment'])) {
                throw new \Magento\Framework\Model\Exception(__('The Comment Text field cannot be empty.'));
            }
            $creditmemo = $this->_initCreditmemo();
            $comment = $creditmemo->addComment(
                $data['comment'],
                isset($data['is_customer_notified']),
                isset($data['is_visible_on_front'])
            );
            $comment->save();
            $creditmemo->sendUpdateEmail(!empty($data['is_customer_notified']), $data['comment']);

            $this->_view->loadLayout();
            $response = $this->_view->getLayout()->getBlock('creditmemo_comments')->toHtml();
        } catch (\Magento\Framework\Model\Exception $e) {
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
     * Create pdf for current creditmemo
     *
     * @return ResponseInterface|void
     */
    public function printAction()
    {
        $this->_initCreditmemo();
        parent::printAction();
    }
}
