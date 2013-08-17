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
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml sales order creditmemo controller
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Sales_Order_CreditmemoController extends Mage_Adminhtml_Controller_Sales_Creditmemo
{
    /**
     * Get requested items qtys and return to stock flags
     */
    protected function _getItemData()
    {
        $data = $this->getRequest()->getParam('creditmemo');
        if (!$data) {
            $data = Mage::getSingleton('Mage_Adminhtml_Model_Session')->getFormData(true);
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
     * @param Mage_Sales_Model_Order $order
     * @return bool
     */
    protected function _canCreditmemo($order)
    {
        /**
         * Check order existing
         */
        if (!$order->getId()) {
            $this->_getSession()->addError($this->__('The order no longer exists.'));
            return false;
        }

        /**
         * Check creditmemo create availability
         */
        if (!$order->canCreditmemo()) {
            $this->_getSession()->addError($this->__('Cannot create credit memo for the order.'));
            return false;
        }
        return true;
    }

    /**
     * Initialize requested invoice instance
     * @param unknown_type $order
     */
    protected function _initInvoice($order)
    {
        $invoiceId = $this->getRequest()->getParam('invoice_id');
        if ($invoiceId) {
            $invoice = Mage::getModel('Mage_Sales_Model_Order_Invoice')
                ->load($invoiceId)
                ->setOrder($order);
            if ($invoice->getId()) {
                return $invoice;
            }
        }
        return false;
    }

    /**
     * Initialize creditmemo model instance
     *
     * @return Mage_Sales_Model_Order_Creditmemo
     */
    protected function _initCreditmemo($update = false)
    {
        $this->_title($this->__('Credit Memos'));

        $creditmemo = false;
        $creditmemoId = $this->getRequest()->getParam('creditmemo_id');
        $orderId = $this->getRequest()->getParam('order_id');
        if ($creditmemoId) {
            $creditmemo = $this->_objectManager->create('Mage_Sales_Model_Order_Creditmemo')->load($creditmemoId);
        } elseif ($orderId) {
            $data   = $this->getRequest()->getParam('creditmemo');
            $order  = $this->_objectManager->create('Mage_Sales_Model_Order')->load($orderId);
            $invoice = $this->_initInvoice($order);

            if (!$this->_canCreditmemo($order)) {
                return false;
            }

            $savedData = $this->_getItemData();

            $qtys = array();
            $backToStock = array();
            foreach ($savedData as $orderItemId =>$itemData) {
                if (isset($itemData['qty'])) {
                    $qtys[$orderItemId] = $itemData['qty'];
                }
                if (isset($itemData['back_to_stock'])) {
                    $backToStock[$orderItemId] = true;
                }
            }
            $data['qtys'] = $qtys;

            $service = $this->_objectManager->create('Mage_Sales_Model_Service_Order', array('order' => $order));
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
                        $this->_objectManager->get('Mage_CatalogInventory_Helper_Data')->isAutoReturnEnabled()
                    );
                } else {
                    $creditmemoItem->setBackToStock(false);
                }
            }
        }

        $args = array('creditmemo' => $creditmemo, 'request' => $this->getRequest());
        $this->_eventManager->dispatch('adminhtml_sales_order_creditmemo_register_before', $args);

        $this->_objectManager->get('Mage_Core_Model_Registry')->register('current_creditmemo', $creditmemo);
        return $creditmemo;
    }

    /**
     * Save creditmemo and related order, invoice in one transaction
     * @param Mage_Sales_Model_Order_Creditmemo $creditmemo
     */
    protected function _saveCreditmemo($creditmemo)
    {
        $transactionSave = Mage::getModel('Mage_Core_Model_Resource_Transaction')
            ->addObject($creditmemo)
            ->addObject($creditmemo->getOrder());
        if ($creditmemo->getInvoice()) {
            $transactionSave->addObject($creditmemo->getInvoice());
        }
        $transactionSave->save();

        return $this;
    }

    /**
     * creditmemo information page
     */
    public function viewAction()
    {
        $creditmemo = $this->_initCreditmemo();
        if ($creditmemo) {
            if ($creditmemo->getInvoice()) {
                $this->_title($this->__("View Memo for #%s", $creditmemo->getInvoice()->getIncrementId()));
            } else {
                $this->_title($this->__("View Memo"));
            }

            $this->loadLayout();
            $this->getLayout()->getBlock('sales_creditmemo_view')
                ->updateBackButtonUrl($this->getRequest()->getParam('come_from'));
            $this->_setActiveMenu('Mage_Sales::sales_creditmemo')
                ->renderLayout();
        } else {
            $this->_forward('noRoute');
        }
    }

    /**
     * Start create creditmemo action
     */
    public function startAction()
    {
        /**
         * Clear old values for creditmemo qty's
         */
        $this->_redirect('*/*/new', array('_current'=>true));
    }

    /**
     * creditmemo create page
     */
    public function newAction()
    {
        if ($creditmemo = $this->_initCreditmemo()) {
            if ($creditmemo->getInvoice()) {
                $this->_title($this->__("New Memo for #%s", $creditmemo->getInvoice()->getIncrementId()));
            } else {
                $this->_title($this->__("New Memo"));
            }

            if ($comment = Mage::getSingleton('Mage_Adminhtml_Model_Session')->getCommentText(true)) {
                $creditmemo->setCommentText($comment);
            }

            $this->loadLayout()
                ->_setActiveMenu('Mage_Sales::sales_order')
                ->renderLayout();
        } else {
            $this->_forward('noRoute');
        }
    }

    /**
     * Update items qty action
     */
    public function updateQtyAction()
    {
        try {
            $creditmemo = $this->_initCreditmemo(true);
            $this->loadLayout();
            $response = $this->getLayout()->getBlock('order_items')->toHtml();
        } catch (Mage_Core_Exception $e) {
            $response = array(
                'error'     => true,
                'message'   => $e->getMessage()
            );
            $response = Mage::helper('Mage_Core_Helper_Data')->jsonEncode($response);
        } catch (Exception $e) {
            $response = array(
                'error'     => true,
                'message'   => $this->__('Cannot update the item\'s quantity.')
            );
            $response = Mage::helper('Mage_Core_Helper_Data')->jsonEncode($response);
        }
        $this->getResponse()->setBody($response);
    }

    /**
     * Save creditmemo
     * We can save only new creditmemo. Existing creditmemos are not editable
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
                if (($creditmemo->getGrandTotal() <=0) && (!$creditmemo->getAllowZeroGrandTotal())) {
                    throw new Mage_Core_Exception(
                        $this->__('Credit memo\'s total must be positive.')
                    );
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
                        throw new Mage_Core_Exception(
                            $this->__('Cannot create online refund for Refund to Store Credit.')
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
                $this->_getSession()->addSuccess($this->__('You created the credit memo.'));
                $this->_getSession()->getCommentText(true);
                $this->_redirect('*/sales_order/view', array('order_id' => $creditmemo->getOrderId()));
                return;
            } else {
                $this->_forward('noRoute');
                return;
            }
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            $this->_getSession()->setFormData($data);
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addError($this->__('Cannot save the credit memo.'));
        }
        $this->_redirect('*/*/new', array('_current' => true));
    }

    /**
     * Cancel creditmemo action
     */
    public function cancelAction()
    {
        $creditmemo = $this->_initCreditmemo();
        if ($creditmemo) {
            try {
                $creditmemo->cancel();
                $this->_saveCreditmemo($creditmemo);
                $this->_getSession()->addSuccess($this->__('The credit memo has been canceled.'));
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addError($this->__('You canceled the credit memo.'));
            }
            $this->_redirect('*/*/view', array('creditmemo_id'=>$creditmemo->getId()));
        } else {
            $this->_forward('noRoute');
        }
    }

    /**
     * Void creditmemo action
     */
    public function voidAction()
    {
        $creditmemo = $this->_initCreditmemo();
        if ($creditmemo) {
            try {
                $creditmemo->void();
                $this->_saveCreditmemo($creditmemo);
                $this->_getSession()->addSuccess($this->__('You voided the credit memo.'));
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addError($this->__('We can\'t void the credit memo.'));
            }
            $this->_redirect('*/*/view', array('creditmemo_id'=>$creditmemo->getId()));
        } else {
            $this->_forward('noRoute');
        }
    }

    /**
     * Add comment to creditmemo history
     */
    public function addCommentAction()
    {
        try {
            $this->getRequest()->setParam(
                'creditmemo_id',
                $this->getRequest()->getParam('id')
            );
            $data = $this->getRequest()->getPost('comment');
            if (empty($data['comment'])) {
                Mage::throwException($this->__('The Comment Text field cannot be empty.'));
            }
            $creditmemo = $this->_initCreditmemo();
            $comment = $creditmemo->addComment(
                $data['comment'],
                isset($data['is_customer_notified']),
                isset($data['is_visible_on_front'])
            );
            $comment->save();
            $creditmemo->sendUpdateEmail(!empty($data['is_customer_notified']), $data['comment']);

            $this->loadLayout();
            $response = $this->getLayout()->getBlock('creditmemo_comments')->toHtml();
        } catch (Mage_Core_Exception $e) {
            $response = array(
                'error'     => true,
                'message'   => $e->getMessage()
            );
            $response = Mage::helper('Mage_Core_Helper_Data')->jsonEncode($response);
        } catch (Exception $e) {
            $response = array(
                'error'     => true,
                'message'   => $this->__('Cannot add new comment.')
            );
            $response = Mage::helper('Mage_Core_Helper_Data')->jsonEncode($response);
        }
        $this->getResponse()->setBody($response);
    }

    /**
     * Create pdf for current creditmemo
     */
    public function printAction()
    {
        $this->_initCreditmemo();
        parent::printAction();
    }
}
