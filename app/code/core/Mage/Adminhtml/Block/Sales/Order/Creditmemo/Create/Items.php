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
 * Adminhtml creditmemo items grid
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Mage_Adminhtml_Block_Sales_Order_Creditmemo_Create_Items extends Mage_Adminhtml_Block_Sales_Items_Abstract
{
    protected $_canReturnToStock;
    /**
     * Prepare child blocks
     *
     * @return Mage_Adminhtml_Block_Sales_Order_Creditmemo_Create_Items
     */
    protected function _prepareLayout()
    {
        $onclick = "submitAndReloadArea($('creditmemo_item_container'),'".$this->getUpdateUrl()."')";
        $this->addChild('update_button', 'Mage_Adminhtml_Block_Widget_Button', array(
            'label'     => Mage::helper('Mage_Sales_Helper_Data')->__('Update Qty\'s'),
            'class'     => 'update-button',
            'onclick'   => $onclick,
        ));

        if ($this->getCreditmemo()->canRefund()) {
            if ($this->getCreditmemo()->getInvoice() && $this->getCreditmemo()->getInvoice()->getTransactionId()) {
                $this->addChild('submit_button', 'Mage_Adminhtml_Block_Widget_Button', array(
                    'label'     => Mage::helper('Mage_Sales_Helper_Data')->__('Refund'),
                    'class'     => 'save submit-button',
                    'onclick'   => 'disableElements(\'submit-button\');submitCreditMemo()',
                ));
            }
            $this->addChild('submit_offline', 'Mage_Adminhtml_Block_Widget_Button', array(
                'label'     => Mage::helper('Mage_Sales_Helper_Data')->__('Refund Offline'),
                'class'     => 'save submit-button',
                'onclick'   => 'disableElements(\'submit-button\');submitCreditMemoOffline()',
            ));

        }
        else {
            $this->addChild('submit_button', 'Mage_Adminhtml_Block_Widget_Button', array(
                'label'     => Mage::helper('Mage_Sales_Helper_Data')->__('Refund Offline'),
                'class'     => 'save submit-button',
                'onclick'   => 'disableElements(\'submit-button\');submitCreditMemoOffline()',
            ));
        }

        return parent::_prepareLayout();
    }

    /**
     * Retrieve invoice order
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        return $this->getCreditmemo()->getOrder();
    }

    /**
     * Retrieve source
     *
     * @return Mage_Sales_Model_Order_Creditmemo
     */
    public function getSource()
    {
        return $this->getCreditmemo();
    }

    /**
     * Retrieve order totals block settings
     *
     * @return array
     */
    public function getOrderTotalData()
    {
        return array();
    }

    /**
     * Retrieve order totalbar block data
     *
     * @return array
     */
    public function getOrderTotalbarData()
    {
        $totalbarData = array();
        $this->setPriceDataObject($this->getOrder());
        $totalbarData[] = array(Mage::helper('Mage_Sales_Helper_Data')->__('Paid Amount'), $this->displayPriceAttribute('total_invoiced'), false);
        $totalbarData[] = array(Mage::helper('Mage_Sales_Helper_Data')->__('Refund Amount'), $this->displayPriceAttribute('total_refunded'), false);
        $totalbarData[] = array(Mage::helper('Mage_Sales_Helper_Data')->__('Shipping Amount'), $this->displayPriceAttribute('shipping_invoiced'), false);
        $totalbarData[] = array(Mage::helper('Mage_Sales_Helper_Data')->__('Shipping Refund'), $this->displayPriceAttribute('shipping_refunded'), false);
        $totalbarData[] = array(Mage::helper('Mage_Sales_Helper_Data')->__('Order Grand Total'), $this->displayPriceAttribute('grand_total'), true);

        return $totalbarData;
    }

    /**
     * Retrieve creditmemo model instance
     *
     * @return Mage_Sales_Model_Creditmemo
     */
    public function getCreditmemo()
    {
        return Mage::registry('current_creditmemo');
    }

    public function canEditQty()
    {
        if ($this->getCreditmemo()->getOrder()->getPayment()->canRefund()) {
            return $this->getCreditmemo()->getOrder()->getPayment()->canRefundPartialPerInvoice();
        }
        return true;
    }

    public function getUpdateButtonHtml()
    {
        return $this->getChildHtml('update_button');
    }

    public function getUpdateUrl()
    {
        return $this->getUrl('*/*/updateQty', array(
                'order_id'=>$this->getCreditmemo()->getOrderId(),
                'invoice_id'=>$this->getRequest()->getParam('invoice_id', null),
        ));
    }

    public function canReturnToStock()
    {
        $canReturnToStock = Mage::getStoreConfig(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_CAN_SUBTRACT);
        if (Mage::getStoreConfig(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_CAN_SUBTRACT)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Whether to show 'Return to stock' column in creaditmemo grid
     * @return bool
     */
    public function canReturnItemsToStock()
    {
        if (is_null($this->_canReturnToStock)) {
            if ($this->_canReturnToStock = Mage::getStoreConfig(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_CAN_SUBTRACT)) {
                $canReturnToStock = false;
                foreach ($this->getCreditmemo()->getAllItems() as $item) {
                    $product = Mage::getModel('Mage_Catalog_Model_Product')->load($item->getOrderItem()->getProductId());
                    if ( $product->getId() && $product->getStockItem()->getManageStock() ) {
                        $item->setCanReturnToStock($canReturnToStock = true);
                    } else {
                        $item->setCanReturnToStock(false);
                    }
                }
                $this->getCreditmemo()->getOrder()->setCanReturnToStock($this->_canReturnToStock = $canReturnToStock);
            }
        }
        return $this->_canReturnToStock;
    }

    public function canSendCreditmemoEmail()
    {
        return Mage::helper('Mage_Sales_Helper_Data')->canSendNewCreditmemoEmail($this->getOrder()->getStore()->getId());
    }
}
