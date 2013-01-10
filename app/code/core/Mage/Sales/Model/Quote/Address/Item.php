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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Enter description here ...
 *
 * @method Mage_Sales_Model_Resource_Quote_Address_Item _getResource()
 * @method Mage_Sales_Model_Resource_Quote_Address_Item getResource()
 * @method int getParentItemId()
 * @method Mage_Sales_Model_Quote_Address_Item setParentItemId(int $value)
 * @method int getQuoteAddressId()
 * @method Mage_Sales_Model_Quote_Address_Item setQuoteAddressId(int $value)
 * @method int getQuoteItemId()
 * @method Mage_Sales_Model_Quote_Address_Item setQuoteItemId(int $value)
 * @method string getCreatedAt()
 * @method Mage_Sales_Model_Quote_Address_Item setCreatedAt(string $value)
 * @method string getUpdatedAt()
 * @method Mage_Sales_Model_Quote_Address_Item setUpdatedAt(string $value)
 * @method string getAppliedRuleIds()
 * @method Mage_Sales_Model_Quote_Address_Item setAppliedRuleIds(string $value)
 * @method string getAdditionalData()
 * @method Mage_Sales_Model_Quote_Address_Item setAdditionalData(string $value)
 * @method float getWeight()
 * @method Mage_Sales_Model_Quote_Address_Item setWeight(float $value)
 * @method Mage_Sales_Model_Quote_Address_Item setQty(float $value)
 * @method float getDiscountAmount()
 * @method Mage_Sales_Model_Quote_Address_Item setDiscountAmount(float $value)
 * @method Mage_Sales_Model_Quote_Address_Item setTaxAmount(float $value)
 * @method float getRowTotal()
 * @method Mage_Sales_Model_Quote_Address_Item setRowTotal(float $value)
 * @method float getBaseRowTotal()
 * @method Mage_Sales_Model_Quote_Address_Item setBaseRowTotal(float $value)
 * @method float getRowTotalWithDiscount()
 * @method Mage_Sales_Model_Quote_Address_Item setRowTotalWithDiscount(float $value)
 * @method float getBaseDiscountAmount()
 * @method Mage_Sales_Model_Quote_Address_Item setBaseDiscountAmount(float $value)
 * @method Mage_Sales_Model_Quote_Address_Item setBaseTaxAmount(float $value)
 * @method float getRowWeight()
 * @method Mage_Sales_Model_Quote_Address_Item setRowWeight(float $value)
 * @method int getProductId()
 * @method Mage_Sales_Model_Quote_Address_Item setProductId(int $value)
 * @method int getSuperProductId()
 * @method Mage_Sales_Model_Quote_Address_Item setSuperProductId(int $value)
 * @method int getParentProductId()
 * @method Mage_Sales_Model_Quote_Address_Item setParentProductId(int $value)
 * @method string getSku()
 * @method Mage_Sales_Model_Quote_Address_Item setSku(string $value)
 * @method string getImage()
 * @method Mage_Sales_Model_Quote_Address_Item setImage(string $value)
 * @method string getName()
 * @method Mage_Sales_Model_Quote_Address_Item setName(string $value)
 * @method string getDescription()
 * @method Mage_Sales_Model_Quote_Address_Item setDescription(string $value)
 * @method int getFreeShipping()
 * @method Mage_Sales_Model_Quote_Address_Item setFreeShipping(int $value)
 * @method int getIsQtyDecimal()
 * @method Mage_Sales_Model_Quote_Address_Item setIsQtyDecimal(int $value)
 * @method float getDiscountPercent()
 * @method Mage_Sales_Model_Quote_Address_Item setDiscountPercent(float $value)
 * @method int getNoDiscount()
 * @method Mage_Sales_Model_Quote_Address_Item setNoDiscount(int $value)
 * @method float getTaxPercent()
 * @method Mage_Sales_Model_Quote_Address_Item setTaxPercent(float $value)
 * @method float getBasePrice()
 * @method Mage_Sales_Model_Quote_Address_Item setBasePrice(float $value)
 * @method float getBaseCost()
 * @method Mage_Sales_Model_Quote_Address_Item setBaseCost(float $value)
 * @method float getPriceInclTax()
 * @method Mage_Sales_Model_Quote_Address_Item setPriceInclTax(float $value)
 * @method float getBasePriceInclTax()
 * @method Mage_Sales_Model_Quote_Address_Item setBasePriceInclTax(float $value)
 * @method float getRowTotalInclTax()
 * @method Mage_Sales_Model_Quote_Address_Item setRowTotalInclTax(float $value)
 * @method float getBaseRowTotalInclTax()
 * @method Mage_Sales_Model_Quote_Address_Item setBaseRowTotalInclTax(float $value)
 * @method int getGiftMessageId()
 * @method Mage_Sales_Model_Quote_Address_Item setGiftMessageId(int $value)
 * @method float getHiddenTaxAmount()
 * @method Mage_Sales_Model_Quote_Address_Item setHiddenTaxAmount(float $value)
 * @method float getBaseHiddenTaxAmount()
 * @method Mage_Sales_Model_Quote_Address_Item setBaseHiddenTaxAmount(float $value)
 *
 * @category    Mage
 * @package     Mage_Sales
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Sales_Model_Quote_Address_Item extends Mage_Sales_Model_Quote_Item_Abstract
{
    /**
     * Quote address model object
     *
     * @var Mage_Sales_Model_Quote_Address
     */
    protected $_address;
    protected $_quote;

    protected function _construct()
    {
        $this->_init('Mage_Sales_Model_Resource_Quote_Address_Item');
    }

    protected function _beforeSave()
    {
        parent::_beforeSave();
        if ($this->getAddress()) {
            $this->setQuoteAddressId($this->getAddress()->getId());
        }
        return $this;
    }

    /**
     * Declare address model
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @return  Mage_Sales_Model_Quote_Address_Item
     */
    public function setAddress(Mage_Sales_Model_Quote_Address $address)
    {
        $this->_address = $address;
        $this->_quote   = $address->getQuote();
        return $this;
    }

    /**
     * Retrieve address model
     *
     * @return Mage_Sales_Model_Quote_Address
     */
    public function getAddress()
    {
        return $this->_address;
    }

    /**
     * Retrieve quote model instance
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        return $this->_quote;
    }


    public function importQuoteItem(Mage_Sales_Model_Quote_Item $quoteItem)
    {
        $this->_quote = $quoteItem->getQuote();
        $this->setQuoteItem($quoteItem)
            ->setQuoteItemId($quoteItem->getId())
            ->setProductId($quoteItem->getProductId())
            ->setProduct($quoteItem->getProduct())
            ->setSku($quoteItem->getSku())
            ->setName($quoteItem->getName())
            ->setDescription($quoteItem->getDescription())
            ->setWeight($quoteItem->getWeight())
            ->setPrice($quoteItem->getPrice())
            ->setCost($quoteItem->getCost());

        if (!$this->hasQty()) {
            $this->setQty($quoteItem->getQty());
        }
        $this->setQuoteItemImported(true);
        return $this;
    }

    public function getOptionBycode($code)
    {
        if ($this->getQuoteItem()) {
            return $this->getQuoteItem()->getOptionBycode($code);
        }
        return null;
    }
}
