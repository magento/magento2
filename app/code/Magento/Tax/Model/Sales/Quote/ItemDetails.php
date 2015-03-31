<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\Sales\Quote;

use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Tax\Api\Data\QuoteDetailsItemInterface;

/**
 * @codeCoverageIgnore
 */
class ItemDetails extends AbstractExtensibleModel implements QuoteDetailsItemInterface
{
    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return $this->getData(QuoteDetailsItemInterface::KEY_CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->getData(QuoteDetailsItemInterface::KEY_TYPE);
    }

    /**
     * {@inheritdoc}
     */
    public function getTaxClassKey()
    {
        return $this->getData(QuoteDetailsItemInterface::KEY_TAX_CLASS_KEY);
    }

    /**
     * {@inheritdoc}
     */
    public function getUnitPrice()
    {
        return $this->getData(QuoteDetailsItemInterface::KEY_UNIT_PRICE);
    }

    /**
     * {@inheritdoc}
     */
    public function getQuantity()
    {
        return $this->getData(QuoteDetailsItemInterface::KEY_QUANTITY);
    }

    /**
     * {@inheritdoc}
     */
    public function getTaxIncluded()
    {
        return $this->getData(QuoteDetailsItemInterface::KEY_TAX_INCLUDED);
    }

    /**
     * {@inheritdoc}
     */
    public function getShortDescription()
    {
        return $this->getData(QuoteDetailsItemInterface::KEY_SHORT_DESCRIPTION);
    }

    /**
     * {@inheritdoc}
     */
    public function getDiscountAmount()
    {
        return $this->getData(QuoteDetailsItemInterface::KEY_DISCOUNT_AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function getParentCode()
    {
        return $this->getData(QuoteDetailsItemInterface::KEY_PARENT_CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function getAssociatedItemCode()
    {
        return $this->getData(QuoteDetailsItemInterface::KEY_ASSOCIATED_ITEM_CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function getTaxClassId()
    {
        return $this->getData(QuoteDetailsItemInterface::KEY_TAX_CLASS_ID);
    }

    /**
     * Set code (sku or shipping code)
     *
     * @param string $code
     * @return $this
     */
    public function setCode($code)
    {
        return $this->setData(QuoteDetailsItemInterface::KEY_CODE, $code);
    }

    /**
     * Set type (e.g., shipping, product, wee, gift wrapping, etc.)
     *
     * @param string $type
     * @return $this
     */
    public function setType($type)
    {
        return $this->setData(QuoteDetailsItemInterface::KEY_TYPE, $type);
    }

    /**
     * Set tax class key
     *
     * @param \Magento\Tax\Api\Data\TaxClassKeyInterface $taxClassKey
     * @return $this
     */
    public function setTaxClassKey(\Magento\Tax\Api\Data\TaxClassKeyInterface $taxClassKey = null)
    {
        return $this->setData(QuoteDetailsItemInterface::KEY_TAX_CLASS_KEY, $taxClassKey);
    }

    /**
     * Set unit price
     *
     * @param float $unitPrice
     * @return $this
     */
    public function setUnitPrice($unitPrice)
    {
        return $this->setData(QuoteDetailsItemInterface::KEY_UNIT_PRICE, $unitPrice);
    }

    /**
     * Set quantity
     *
     * @param float $quantity
     * @return $this
     */
    public function setQuantity($quantity)
    {
        return $this->setData(QuoteDetailsItemInterface::KEY_QUANTITY, $quantity);
    }

    /**
     * Set whether the tax is included in the unit price and row total
     *
     * @param bool $isTaxIncluded
     * @return $this
     */
    public function setIsTaxIncluded($isTaxIncluded)
    {
        return $this->setData(QuoteDetailsItemInterface::KEY_TAX_INCLUDED, $isTaxIncluded);
    }

    /**
     * Set short description
     *
     * @param string $shortDescription
     * @return $this
     */
    public function setShortDescription($shortDescription)
    {
        return $this->setData(QuoteDetailsItemInterface::KEY_SHORT_DESCRIPTION, $shortDescription);
    }

    /**
     * Set discount amount
     *
     * @param float $discountAmount
     * @return $this
     */
    public function setDiscountAmount($discountAmount)
    {
        return $this->setData(QuoteDetailsItemInterface::KEY_DISCOUNT_AMOUNT, $discountAmount);
    }

    /**
     * Set parent code
     *
     * @param string $parentCode
     * @return $this
     */
    public function setParentCode($parentCode)
    {
        return $this->setData(QuoteDetailsItemInterface::KEY_PARENT_CODE, $parentCode);
    }

    /**
     * Set associated item code
     *
     * @param int $associatedItemCode
     * @return $this
     */
    public function setAssociatedItemCode($associatedItemCode)
    {
        return $this->setData(QuoteDetailsItemInterface::KEY_ASSOCIATED_ITEM_CODE, $associatedItemCode);
    }

    /**
     * Set associated item tax class id
     *
     * @param int $taxClassId
     * @return $this
     */
    public function setTaxClassId($taxClassId)
    {
        return $this->setData(QuoteDetailsItemInterface::KEY_TAX_CLASS_ID, $taxClassId);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Tax\Api\Data\QuoteDetailsItemExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Tax\Api\Data\QuoteDetailsItemExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Tax\Api\Data\QuoteDetailsItemExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
