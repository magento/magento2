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
 * @package     Mage_Sales
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Enter description here ...
 *
 * @method Mage_Sales_Model_Resource_Order_Creditmemo_Item _getResource()
 * @method Mage_Sales_Model_Resource_Order_Creditmemo_Item getResource()
 * @method int getParentId()
 * @method Mage_Sales_Model_Order_Creditmemo_Item setParentId(int $value)
 * @method float getWeeeTaxAppliedRowAmount()
 * @method Mage_Sales_Model_Order_Creditmemo_Item setWeeeTaxAppliedRowAmount(float $value)
 * @method float getBasePrice()
 * @method Mage_Sales_Model_Order_Creditmemo_Item setBasePrice(float $value)
 * @method float getBaseWeeeTaxRowDisposition()
 * @method Mage_Sales_Model_Order_Creditmemo_Item setBaseWeeeTaxRowDisposition(float $value)
 * @method float getTaxAmount()
 * @method Mage_Sales_Model_Order_Creditmemo_Item setTaxAmount(float $value)
 * @method float getBaseWeeeTaxAppliedAmount()
 * @method Mage_Sales_Model_Order_Creditmemo_Item setBaseWeeeTaxAppliedAmount(float $value)
 * @method float getWeeeTaxRowDisposition()
 * @method Mage_Sales_Model_Order_Creditmemo_Item setWeeeTaxRowDisposition(float $value)
 * @method float getBaseRowTotal()
 * @method Mage_Sales_Model_Order_Creditmemo_Item setBaseRowTotal(float $value)
 * @method float getDiscountAmount()
 * @method Mage_Sales_Model_Order_Creditmemo_Item setDiscountAmount(float $value)
 * @method float getRowTotal()
 * @method Mage_Sales_Model_Order_Creditmemo_Item setRowTotal(float $value)
 * @method float getWeeeTaxAppliedAmount()
 * @method Mage_Sales_Model_Order_Creditmemo_Item setWeeeTaxAppliedAmount(float $value)
 * @method float getBaseDiscountAmount()
 * @method Mage_Sales_Model_Order_Creditmemo_Item setBaseDiscountAmount(float $value)
 * @method float getBaseWeeeTaxDisposition()
 * @method Mage_Sales_Model_Order_Creditmemo_Item setBaseWeeeTaxDisposition(float $value)
 * @method float getPriceInclTax()
 * @method Mage_Sales_Model_Order_Creditmemo_Item setPriceInclTax(float $value)
 * @method float getBaseTaxAmount()
 * @method Mage_Sales_Model_Order_Creditmemo_Item setBaseTaxAmount(float $value)
 * @method float getWeeeTaxDisposition()
 * @method Mage_Sales_Model_Order_Creditmemo_Item setWeeeTaxDisposition(float $value)
 * @method float getBasePriceInclTax()
 * @method Mage_Sales_Model_Order_Creditmemo_Item setBasePriceInclTax(float $value)
 * @method float getQty()
 * @method float getBaseCost()
 * @method Mage_Sales_Model_Order_Creditmemo_Item setBaseCost(float $value)
 * @method float getBaseWeeeTaxAppliedRowAmnt()
 * @method Mage_Sales_Model_Order_Creditmemo_Item setBaseWeeeTaxAppliedRowAmnt(float $value)
 * @method float getPrice()
 * @method Mage_Sales_Model_Order_Creditmemo_Item setPrice(float $value)
 * @method float getBaseRowTotalInclTax()
 * @method Mage_Sales_Model_Order_Creditmemo_Item setBaseRowTotalInclTax(float $value)
 * @method float getRowTotalInclTax()
 * @method Mage_Sales_Model_Order_Creditmemo_Item setRowTotalInclTax(float $value)
 * @method int getProductId()
 * @method Mage_Sales_Model_Order_Creditmemo_Item setProductId(int $value)
 * @method int getOrderItemId()
 * @method Mage_Sales_Model_Order_Creditmemo_Item setOrderItemId(int $value)
 * @method string getAdditionalData()
 * @method Mage_Sales_Model_Order_Creditmemo_Item setAdditionalData(string $value)
 * @method string getDescription()
 * @method Mage_Sales_Model_Order_Creditmemo_Item setDescription(string $value)
 * @method string getWeeeTaxApplied()
 * @method Mage_Sales_Model_Order_Creditmemo_Item setWeeeTaxApplied(string $value)
 * @method string getSku()
 * @method Mage_Sales_Model_Order_Creditmemo_Item setSku(string $value)
 * @method string getName()
 * @method Mage_Sales_Model_Order_Creditmemo_Item setName(string $value)
 * @method float getHiddenTaxAmount()
 * @method Mage_Sales_Model_Order_Creditmemo_Item setHiddenTaxAmount(float $value)
 * @method float getBaseHiddenTaxAmount()
 * @method Mage_Sales_Model_Order_Creditmemo_Item setBaseHiddenTaxAmount(float $value)
 *
 * @category    Mage
 * @package     Mage_Sales
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Sales_Model_Order_Creditmemo_Item extends Mage_Core_Model_Abstract
{
    protected $_eventPrefix = 'sales_creditmemo_item';
    protected $_eventObject = 'creditmemo_item';
    protected $_creditmemo = null;
    protected $_orderItem = null;

    /**
     * Initialize resource model
     */
    function _construct()
    {
        $this->_init('Mage_Sales_Model_Resource_Order_Creditmemo_Item');
    }

    /**
     * Declare creditmemo instance
     *
     * @param   Mage_Sales_Model_Order_Creditmemo $creditmemo
     * @return  Mage_Sales_Model_Order_Creditmemo_Item
     */
    public function setCreditmemo(Mage_Sales_Model_Order_Creditmemo $creditmemo)
    {
        $this->_creditmemo = $creditmemo;
        return $this;
    }

    /**
     * Retrieve creditmemo instance
     *
     * @return Mage_Sales_Model_Order_Creditmemo
     */
    public function getCreditmemo()
    {
        return $this->_creditmemo;
    }

    /**
     * Declare order item instance
     *
     * @param   Mage_Sales_Model_Order_Item $item
     * @return  Mage_Sales_Model_Order_Creditmemo_Item
     */
    public function setOrderItem(Mage_Sales_Model_Order_Item $item)
    {
        $this->_orderItem = $item;
        $this->setOrderItemId($item->getId());
        return $this;
    }

    /**
     * Retrieve order item instance
     *
     * @return Mage_Sales_Model_Order_Item
     */
    public function getOrderItem()
    {
        if (is_null($this->_orderItem)) {
            if ($this->getCreditmemo()) {
                $this->_orderItem = $this->getCreditmemo()->getOrder()->getItemById($this->getOrderItemId());
            }
            else {
                $this->_orderItem = Mage::getModel('Mage_Sales_Model_Order_Item')
                    ->load($this->getOrderItemId());
            }
        }
        return $this->_orderItem;
    }

    /**
     * Declare qty
     *
     * @param   float $qty
     * @return  Mage_Sales_Model_Order_Creditmemo_Item
     */
    public function setQty($qty)
    {
        if ($this->getOrderItem()->getIsQtyDecimal()) {
            $qty = (float) $qty;
        }
        else {
            $qty = (int) $qty;
        }
        $qty = $qty > 0 ? $qty : 0;
        /**
         * Check qty availability
         */
        if ($qty <= $this->getOrderItem()->getQtyToRefund() || $this->getOrderItem()->isDummy()) {
            $this->setData('qty', $qty);
        }
        else {
            Mage::throwException(
                Mage::helper('Mage_Sales_Helper_Data')->__('Invalid qty to refund item "%s"', $this->getName())
            );
        }
        return $this;
    }

    /**
     * Applying qty to order item
     *
     * @return Mage_Sales_Model_Order_Shipment_Item
     */
    public function register()
    {
        $orderItem = $this->getOrderItem();

        $orderItem->setQtyRefunded($orderItem->getQtyRefunded() + $this->getQty());
        $orderItem->setTaxRefunded($orderItem->getTaxRefunded() + $this->getTaxAmount());
        $orderItem->setBaseTaxRefunded($orderItem->getBaseTaxRefunded() + $this->getBaseTaxAmount());
        $orderItem->setHiddenTaxRefunded($orderItem->getHiddenTaxRefunded() + $this->getHiddenTaxAmount());
        $orderItem->setBaseHiddenTaxRefunded($orderItem->getBaseHiddenTaxRefunded() + $this->getBaseHiddenTaxAmount());
        $orderItem->setAmountRefunded($orderItem->getAmountRefunded() + $this->getRowTotal());
        $orderItem->setBaseAmountRefunded($orderItem->getBaseAmountRefunded() + $this->getBaseRowTotal());
        $orderItem->setDiscountRefunded($orderItem->getDiscountRefunded() + $this->getDiscountAmount());
        $orderItem->setBaseDiscountRefunded($orderItem->getBaseDiscountRefunded() + $this->getBaseDiscountAmount());

        return $this;
    }

    public function cancel()
    {
        $this->getOrderItem()->setQtyRefunded(
            $this->getOrderItem()->getQtyRefunded()-$this->getQty()
        );
        $this->getOrderItem()->setTaxRefunded(
            $this->getOrderItem()->getTaxRefunded()
                - $this->getOrderItem()->getBaseTaxAmount() * $this->getQty() / $this->getOrderItem()->getQtyOrdered()
        );
        $this->getOrderItem()->setHiddenTaxRefunded(
            $this->getOrderItem()->getHiddenTaxRefunded()
                - $this->getOrderItem()->getHiddenTaxAmount() * $this->getQty() / $this->getOrderItem()->getQtyOrdered()
        );
        return $this;
    }

    /**
     * Invoice item row total calculation
     *
     * @return Mage_Sales_Model_Order_Invoice_Item
     */
    public function calcRowTotal()
    {
        $creditmemo           = $this->getCreditmemo();
        $orderItem            = $this->getOrderItem();
        $orderItemQtyInvoiced = $orderItem->getQtyInvoiced();

        $rowTotal            = $orderItem->getRowInvoiced() - $orderItem->getAmountRefunded();
        $baseRowTotal        = $orderItem->getBaseRowInvoiced() - $orderItem->getBaseAmountRefunded();
        $rowTotalInclTax     = $orderItem->getRowTotalInclTax();
        $baseRowTotalInclTax = $orderItem->getBaseRowTotalInclTax();

        if (!$this->isLast()) {
            $availableQty = $orderItemQtyInvoiced - $orderItem->getQtyRefunded();
            $rowTotal     = $creditmemo->roundPrice($rowTotal / $availableQty * $this->getQty());
            $baseRowTotal = $creditmemo->roundPrice($baseRowTotal / $availableQty * $this->getQty(), 'base');
        }
        $this->setRowTotal($rowTotal);
        $this->setBaseRowTotal($baseRowTotal);

        if ($rowTotalInclTax && $baseRowTotalInclTax) {
            $orderItemQty = $orderItem->getQtyOrdered();
            $this->setRowTotalInclTax(
                $creditmemo->roundPrice($rowTotalInclTax / $orderItemQty * $this->getQty(), 'including')
            );
            $this->setBaseRowTotalInclTax(
                $creditmemo->roundPrice($baseRowTotalInclTax / $orderItemQty * $this->getQty(), 'including_base')
            );
        }
        return $this;
    }

    /**
     * Checking if the item is last
     *
     * @return bool
     */
    public function isLast()
    {
        $orderItem = $this->getOrderItem();
        if ((string)(float)$this->getQty() == (string)(float)$orderItem->getQtyToRefund()
                && !$orderItem->getQtyToInvoice()) {
            return true;
        }
        return false;
    }

    /**
     * Before object save
     *
     * @return Mage_Sales_Model_Order_Creditmemo_Item
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();

        if (!$this->getParentId() && $this->getCreditmemo()) {
            $this->setParentId($this->getCreditmemo()->getId());
        }

        return $this;
    }
}
