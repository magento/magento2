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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml invoice create
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Sales_Order_Invoice_View extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Admin session
     *
     * @var Mage_Backend_Model_Auth_Session
     */
    protected $_session;

    protected function _construct()
    {
        $this->_objectId    = 'invoice_id';
        $this->_controller  = 'sales_order_invoice';
        $this->_mode        = 'view';
        $this->_session = Mage::getSingleton('Mage_Backend_Model_Auth_Session');

        parent::_construct();

        $this->_removeButton('save');
        $this->_removeButton('reset');
        $this->_removeButton('delete');

        if (!$this->getInvoice()) {
            return;
        }

        if ($this->_isAllowedAction('Mage_Sales::cancel') && $this->getInvoice()->canCancel()) {
            $this->_addButton('cancel', array(
                'label'     => Mage::helper('Mage_Sales_Helper_Data')->__('Cancel'),
                'class'     => 'delete',
                'onclick'   => 'setLocation(\''.$this->getCancelUrl().'\')'
                )
            );
        }

        if ($this->_isAllowedAction('Mage_Sales::emails')) {
            $this->addButton('send_notification', array(
                'label'     => Mage::helper('Mage_Sales_Helper_Data')->__('Send Email'),
                'onclick'   => 'confirmSetLocation(\''
                . Mage::helper('Mage_Sales_Helper_Data')->__('Are you sure you want to send Invoice email to customer?')
                . '\', \'' . $this->getEmailUrl() . '\')'
            ));
        }

        $orderPayment = $this->getInvoice()->getOrder()->getPayment();

        if ($this->_isAllowedAction('Mage_Sales::creditmemo') && $this->getInvoice()->getOrder()->canCreditmemo()) {
            if (($orderPayment->canRefundPartialPerInvoice()
                && $this->getInvoice()->canRefund()
                && $orderPayment->getAmountPaid() > $orderPayment->getAmountRefunded())
                || ($orderPayment->canRefund() && !$this->getInvoice()->getIsUsedForRefund())) {
                $this->_addButton('capture', array( // capture?
                    'label'     => Mage::helper('Mage_Sales_Helper_Data')->__('Credit Memo'),
                    'class'     => 'go',
                    'onclick'   => 'setLocation(\''.$this->getCreditMemoUrl().'\')'
                    )
                );
            }
        }

        if ($this->_isAllowedAction('Mage_Sales::capture') && $this->getInvoice()->canCapture()) {
            $this->_addButton('capture', array(
                'label'     => Mage::helper('Mage_Sales_Helper_Data')->__('Capture'),
                'class'     => 'save',
                'onclick'   => 'setLocation(\''.$this->getCaptureUrl().'\')'
                )
            );
        }

        if ($this->getInvoice()->canVoid()) {
            $this->_addButton('void', array(
                'label'     => Mage::helper('Mage_Sales_Helper_Data')->__('Void'),
                'class'     => 'save',
                'onclick'   => 'setLocation(\''.$this->getVoidUrl().'\')'
                )
            );
        }

        if ($this->getInvoice()->getId()) {
            $this->_addButton('print', array(
                'label'     => Mage::helper('Mage_Sales_Helper_Data')->__('Print'),
                'class'     => 'save',
                'onclick'   => 'setLocation(\''.$this->getPrintUrl().'\')'
                )
            );
        }
    }

    /**
     * Retrieve invoice model instance
     *
     * @return Mage_Sales_Model_Order_Invoice
     */
    public function getInvoice()
    {
        return Mage::registry('current_invoice');
    }

    public function getHeaderText()
    {
        if ($this->getInvoice()->getEmailSent()) {
            $emailSent = Mage::helper('Mage_Sales_Helper_Data')->__('the invoice email was sent');
        }
        else {
            $emailSent = Mage::helper('Mage_Sales_Helper_Data')->__('the invoice email is not sent');
        }
        return Mage::helper('Mage_Sales_Helper_Data')->__('Invoice #%1$s | %2$s | %4$s (%3$s)', $this->getInvoice()->getIncrementId(), $this->getInvoice()->getStateName(), $emailSent, $this->formatDate($this->getInvoice()->getCreatedAtDate(), 'medium', true));
    }

    public function getBackUrl()
    {
        return $this->getUrl(
            '*/sales_order/view',
            array(
                'order_id'  => $this->getInvoice() ? $this->getInvoice()->getOrderId() : null,
                'active_tab'=> 'order_invoices'
            ));
    }

    public function getCaptureUrl()
    {
        return $this->getUrl('*/*/capture', array('invoice_id'=>$this->getInvoice()->getId()));
    }

    public function getVoidUrl()
    {
        return $this->getUrl('*/*/void', array('invoice_id'=>$this->getInvoice()->getId()));
    }

    public function getCancelUrl()
    {
        return $this->getUrl('*/*/cancel', array('invoice_id'=>$this->getInvoice()->getId()));
    }

    public function getEmailUrl()
    {
        return $this->getUrl('*/*/email', array(
            'order_id'  => $this->getInvoice()->getOrder()->getId(),
            'invoice_id'=> $this->getInvoice()->getId(),
        ));
    }

    public function getCreditMemoUrl()
    {
        return $this->getUrl('*/sales_order_creditmemo/start', array(
            'order_id'  => $this->getInvoice()->getOrder()->getId(),
            'invoice_id'=> $this->getInvoice()->getId(),
        ));
    }

    public function getPrintUrl()
    {
        return $this->getUrl('*/*/print', array(
            'invoice_id' => $this->getInvoice()->getId()
        ));
    }

    public function updateBackButtonUrl($flag)
    {
        if ($flag) {
            if ($this->getInvoice()->getBackUrl()) {
                return $this->_updateButton('back', 'onclick', 'setLocation(\'' . $this->getInvoice()->getBackUrl() . '\')');
            }
            return $this->_updateButton('back', 'onclick', 'setLocation(\'' . $this->getUrl('*/sales_invoice/') . '\')');
        }
        return $this;
    }

    /**
     * Check whether is allowed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return Mage::getSingleton('Mage_Core_Model_Authorization')->isAllowed($resourceId);
    }
}
