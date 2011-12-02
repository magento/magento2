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
 * @package     Mage_Checkout
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Nominal total rendered
 *
 * Each item is rendered as separate total with its details
 */
class Mage_Checkout_Block_Total_Nominal extends Mage_Checkout_Block_Total_Default
{
    /**
     * Custom template
     *
     * @var string
     */
    protected $_template = 'total/nominal.phtml';

    /**
     * Getter for a quote item name
     *
     * @param Mage_Sales_Model_Quote_Item_Abstract $quoteItem
     * @return string
     */
    public function getItemName(Mage_Sales_Model_Quote_Item_Abstract $quoteItem)
    {
        return $quoteItem->getName();
    }

    /**
     * Getter for a quote item row total
     *
     * @param Mage_Sales_Model_Quote_Item_Abstract $quoteItem
     * @return float
     */
    public function getItemRowTotal(Mage_Sales_Model_Quote_Item_Abstract $quoteItem)
    {
        return $quoteItem->getNominalRowTotal();
    }

    /**
     * Getter for nominal total item details
     *
     * @param Mage_Sales_Model_Quote_Item_Abstract $quoteItem
     * @return array
     */
    public function getTotalItemDetails(Mage_Sales_Model_Quote_Item_Abstract $quoteItem)
    {
        return $quoteItem->getNominalTotalDetails();
    }

    /**
     * Getter for details row label
     *
     * @param Varien_Object $row
     * @return string
     */
    public function getItemDetailsRowLabel(Varien_Object $row)
    {
        return $row->getLabel();
    }

    /**
     * Getter for details row amount
     *
     * @param Varien_Object $row
     * @return string
     */
    public function getItemDetailsRowAmount(Varien_Object $row)
    {
        return $row->getAmount();
    }

    /**
     * Getter for details row compounded state
     *
     * @param Varien_Object $row
     * @return bool
     */
    public function getItemDetailsRowIsCompounded(Varien_Object $row)
    {
        return $row->getIsCompounded();
    }

    /**
     * Format an amount without container
     *
     * @param float $amount
     * @return string
     */
    public function formatPrice($amount)
    {
        return $this->_store->formatPrice($amount, false);
    }

    /**
     * Import total data into the block, if there are items
     *
     * @return string
     */
    protected function _toHtml()
    {
        $total = $this->getTotal();
        $items = $total->getItems();
        if ($items) {
            foreach ($total->getData() as $key => $value) {
                $this->setData("total_{$key}", $value);
            }
            return parent::_toHtml();
        }
        return '';
    }
}
