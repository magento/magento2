<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Quote\Address;

use Magento\Sales\Model\Quote;

/**
 * @method \Magento\Sales\Model\Resource\Quote\Address\Item _getResource()
 * @method \Magento\Sales\Model\Resource\Quote\Address\Item getResource()
 * @method int getParentItemId()
 * @method \Magento\Sales\Model\Quote\Address\Item setParentItemId(int $value)
 * @method int getQuoteAddressId()
 * @method \Magento\Sales\Model\Quote\Address\Item setQuoteAddressId(int $value)
 * @method int getQuoteItemId()
 * @method \Magento\Sales\Model\Quote\Address\Item setQuoteItemId(int $value)
 * @method string getCreatedAt()
 * @method \Magento\Sales\Model\Quote\Address\Item setCreatedAt(string $value)
 * @method string getUpdatedAt()
 * @method \Magento\Sales\Model\Quote\Address\Item setUpdatedAt(string $value)
 * @method string getAppliedRuleIds()
 * @method \Magento\Sales\Model\Quote\Address\Item setAppliedRuleIds(string $value)
 * @method string getAdditionalData()
 * @method \Magento\Sales\Model\Quote\Address\Item setAdditionalData(string $value)
 * @method float getWeight()
 * @method \Magento\Sales\Model\Quote\Address\Item setWeight(float $value)
 * @method \Magento\Sales\Model\Quote\Address\Item setQty(float $value)
 * @method float getDiscountAmount()
 * @method \Magento\Sales\Model\Quote\Address\Item setDiscountAmount(float $value)
 * @method \Magento\Sales\Model\Quote\Address\Item setTaxAmount(float $value)
 * @method float getRowTotal()
 * @method \Magento\Sales\Model\Quote\Address\Item setRowTotal(float $value)
 * @method float getBaseRowTotal()
 * @method \Magento\Sales\Model\Quote\Address\Item setBaseRowTotal(float $value)
 * @method float getRowTotalWithDiscount()
 * @method \Magento\Sales\Model\Quote\Address\Item setRowTotalWithDiscount(float $value)
 * @method float getBaseDiscountAmount()
 * @method \Magento\Sales\Model\Quote\Address\Item setBaseDiscountAmount(float $value)
 * @method \Magento\Sales\Model\Quote\Address\Item setBaseTaxAmount(float $value)
 * @method float getRowWeight()
 * @method \Magento\Sales\Model\Quote\Address\Item setRowWeight(float $value)
 * @method int getProductId()
 * @method \Magento\Sales\Model\Quote\Address\Item setProductId(int $value)
 * @method int getSuperProductId()
 * @method \Magento\Sales\Model\Quote\Address\Item setSuperProductId(int $value)
 * @method int getParentProductId()
 * @method \Magento\Sales\Model\Quote\Address\Item setParentProductId(int $value)
 * @method string getSku()
 * @method \Magento\Sales\Model\Quote\Address\Item setSku(string $value)
 * @method string getImage()
 * @method \Magento\Sales\Model\Quote\Address\Item setImage(string $value)
 * @method string getName()
 * @method \Magento\Sales\Model\Quote\Address\Item setName(string $value)
 * @method string getDescription()
 * @method \Magento\Sales\Model\Quote\Address\Item setDescription(string $value)
 * @method int getFreeShipping()
 * @method \Magento\Sales\Model\Quote\Address\Item setFreeShipping(int $value)
 * @method int getIsQtyDecimal()
 * @method \Magento\Sales\Model\Quote\Address\Item setIsQtyDecimal(int $value)
 * @method float getDiscountPercent()
 * @method \Magento\Sales\Model\Quote\Address\Item setDiscountPercent(float $value)
 * @method int getNoDiscount()
 * @method \Magento\Sales\Model\Quote\Address\Item setNoDiscount(int $value)
 * @method float getTaxPercent()
 * @method \Magento\Sales\Model\Quote\Address\Item setTaxPercent(float $value)
 * @method float getBasePrice()
 * @method \Magento\Sales\Model\Quote\Address\Item setBasePrice(float $value)
 * @method float getBaseCost()
 * @method \Magento\Sales\Model\Quote\Address\Item setBaseCost(float $value)
 * @method float getPriceInclTax()
 * @method \Magento\Sales\Model\Quote\Address\Item setPriceInclTax(float $value)
 * @method float getBasePriceInclTax()
 * @method \Magento\Sales\Model\Quote\Address\Item setBasePriceInclTax(float $value)
 * @method float getRowTotalInclTax()
 * @method \Magento\Sales\Model\Quote\Address\Item setRowTotalInclTax(float $value)
 * @method float getBaseRowTotalInclTax()
 * @method \Magento\Sales\Model\Quote\Address\Item setBaseRowTotalInclTax(float $value)
 * @method int getGiftMessageId()
 * @method \Magento\Sales\Model\Quote\Address\Item setGiftMessageId(int $value)
 * @method float getHiddenTaxAmount()
 * @method \Magento\Sales\Model\Quote\Address\Item setHiddenTaxAmount(float $value)
 * @method float getBaseHiddenTaxAmount()
 * @method \Magento\Sales\Model\Quote\Address\Item setBaseHiddenTaxAmount(float $value)
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Item extends \Magento\Sales\Model\Quote\Item\AbstractItem
{
    /**
     * Quote address model object
     *
     * @var \Magento\Sales\Model\Quote\Address
     */
    protected $_address;

    /**
     * @var Quote
     */
    protected $_quote;

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Sales\Model\Resource\Quote\Address\Item');
    }

    /**
     * @return $this|\Magento\Sales\Model\Quote\Item\AbstractItem
     */
    public function beforeSave()
    {
        parent::beforeSave();
        if ($this->getAddress()) {
            $this->setQuoteAddressId($this->getAddress()->getId());
        }
        return $this;
    }

    /**
     * Declare address model
     *
     * @param \Magento\Sales\Model\Quote\Address $address
     * @return $this
     */
    public function setAddress(\Magento\Sales\Model\Quote\Address $address)
    {
        $this->_address = $address;
        $this->_quote = $address->getQuote();
        return $this;
    }

    /**
     * Retrieve address model
     *
     * @return \Magento\Sales\Model\Quote\Address
     */
    public function getAddress()
    {
        return $this->_address;
    }

    /**
     * Retrieve quote model instance
     *
     * @return Quote
     */
    public function getQuote()
    {
        return $this->_quote;
    }

    /**
     * @param \Magento\Sales\Model\Quote\Item $quoteItem
     * @return $this
     */
    public function importQuoteItem(\Magento\Sales\Model\Quote\Item $quoteItem)
    {
        $this->_quote = $quoteItem->getQuote();
        $this->setQuoteItem(
            $quoteItem
        )->setQuoteItemId(
            $quoteItem->getId()
        )->setProductId(
            $quoteItem->getProductId()
        )->setProduct(
            $quoteItem->getProduct()
        )->setSku(
            $quoteItem->getSku()
        )->setName(
            $quoteItem->getName()
        )->setDescription(
            $quoteItem->getDescription()
        )->setWeight(
            $quoteItem->getWeight()
        )->setPrice(
            $quoteItem->getPrice()
        )->setCost(
            $quoteItem->getCost()
        );

        if (!$this->hasQty()) {
            $this->setQty($quoteItem->getQty());
        }
        $this->setQuoteItemImported(true);
        return $this;
    }

    /**
     * @param string $code
     * @return \Magento\Catalog\Model\Product\Configuration\Item\Option\OptionInterface|null
     */
    public function getOptionBycode($code)
    {
        if ($this->getQuoteItem()) {
            return $this->getQuoteItem()->getOptionBycode($code);
        }
        return null;
    }
}
