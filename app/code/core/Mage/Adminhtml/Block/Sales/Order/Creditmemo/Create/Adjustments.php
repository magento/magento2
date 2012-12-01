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
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Adminhtml_Block_Sales_Order_Creditmemo_Create_Adjustments extends Mage_Adminhtml_Block_Template
{
    protected $_source;
    /**
     * Initialize creditmemo agjustment totals
     *
     * @return Mage_Tax_Block_Sales_Order_Tax
     */
    public function initTotals()
    {
        $parent = $this->getParentBlock();
        $this->_source  = $parent->getSource();
        $total = new Varien_Object(array(
            'code'      => 'agjustments',
            'block_name'=> $this->getNameInLayout()
        ));
        $parent->removeTotal('shipping');
        $parent->removeTotal('adjustment_positive');
        $parent->removeTotal('adjustment_negative');
        $parent->addTotal($total);
        return $this;
    }

    public function getSource()
    {
        return $this->_source;
    }

    /**
     * Get credit memo shipping amount depend on configuration settings
     * @return float
     */
    public function getShippingAmount()
    {
        $config = Mage::getSingleton('Mage_Tax_Model_Config');
        $source = $this->getSource();
        if ($config->displaySalesShippingInclTax($source->getOrder()->getStoreId())) {
            $shipping = $source->getBaseShippingInclTax();
        } else {
            $shipping = $source->getBaseShippingAmount();
        }
        return Mage::app()->getStore()->roundPrice($shipping) * 1;
    }

    /**
     * Get label for shipping total based on configuration settings
     * @return string
     */
    public function getShippingLabel()
    {
        $config = Mage::getSingleton('Mage_Tax_Model_Config');
        $source = $this->getSource();
        if ($config->displaySalesShippingInclTax($source->getOrder()->getStoreId())) {
            $label = $this->helper('Mage_Sales_Helper_Data')->__('Refund Shipping (Incl. Tax)');
        } elseif ($config->displaySalesShippingBoth($source->getOrder()->getStoreId())) {
            $label = $this->helper('Mage_Sales_Helper_Data')->__('Refund Shipping (Excl. Tax)');
        } else {
            $label = $this->helper('Mage_Sales_Helper_Data')->__('Refund Shipping');
        }
        return $label;
    }
}
