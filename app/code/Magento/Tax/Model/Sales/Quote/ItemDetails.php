<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */
    const KEY_CODE                 = 'code';
    const KEY_TYPE                 = 'type';
    const KEY_TAX_CLASS_KEY        = 'tax_class_key';
    const KEY_UNIT_PRICE           = 'unit_price';
    const KEY_QUANTITY             = 'quantity';
    const KEY_IS_TAX_INCLUDED      = 'is_tax_included';
    const KEY_SHORT_DESCRIPTION    = 'short_description';
    const KEY_DISCOUNT_AMOUNT      = 'discount_amount';
    const KEY_PARENT_CODE          = 'parent_code';
    const KEY_ASSOCIATED_ITEM_CODE = 'associated_item_code';
    const KEY_TAX_CLASS_ID         = 'tax_class_id';
    /**#@-*/

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return $this->getData(self::KEY_CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->getData(self::KEY_TYPE);
    }

    /**
     * {@inheritdoc}
     */
    public function getTaxClassKey()
    {
        return $this->getData(self::KEY_TAX_CLASS_KEY);
    }

    /**
     * {@inheritdoc}
     */
    public function getUnitPrice()
    {
        return $this->getData(self::KEY_UNIT_PRICE);
    }

    /**
     * {@inheritdoc}
     */
    public function getQuantity()
    {
        return $this->getData(self::KEY_QUANTITY);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsTaxIncluded()
    {
        return $this->getData(self::KEY_IS_TAX_INCLUDED);
    }

    /**
     * {@inheritdoc}
     */
    public function getShortDescription()
    {
        return $this->getData(self::KEY_SHORT_DESCRIPTION);
    }

    /**
     * {@inheritdoc}
     */
    public function getDiscountAmount()
    {
        return $this->getData(self::KEY_DISCOUNT_AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function getParentCode()
    {
        return $this->getData(self::KEY_PARENT_CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function getAssociatedItemCode()
    {
        return $this->getData(self::KEY_ASSOCIATED_ITEM_CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function getTaxClassId()
    {
        return $this->getData(self::KEY_TAX_CLASS_ID);
    }

    /**
     * Set code (sku or shipping code)
     *
     * @param string $code
     * @return $this
     */
    public function setCode($code)
    {
        return $this->setData(self::KEY_CODE, $code);
    }

    /**
     * Set type (e.g., shipping, product, wee, gift wrapping, etc.)
     *
     * @param string $type
     * @return $this
     */
    public function setType($type)
    {
        return $this->setData(self::KEY_TYPE, $type);
    }

    /**
     * Set tax class key
     *
     * @param \Magento\Tax\Api\Data\TaxClassKeyInterface $taxClassKey
     * @return $this
     */
    public function setTaxClassKey(\Magento\Tax\Api\Data\TaxClassKeyInterface $taxClassKey = null)
    {
        return $this->setData(self::KEY_TAX_CLASS_KEY, $taxClassKey);
    }

    /**
     * Set unit price
     *
     * @param float $unitPrice
     * @return $this
     */
    public function setUnitPrice($unitPrice)
    {
        return $this->setData(self::KEY_UNIT_PRICE, $unitPrice);
    }

    /**
     * Set quantity
     *
     * @param float $quantity
     * @return $this
     */
    public function setQuantity($quantity)
    {
        return $this->setData(self::KEY_QUANTITY, $quantity);
    }

    /**
     * Set whether the tax is included in the unit price and row total
     *
     * @param bool $isTaxIncluded
     * @return $this
     */
    public function setIsTaxIncluded($isTaxIncluded)
    {
        return $this->setData(self::KEY_IS_TAX_INCLUDED, $isTaxIncluded);
    }

    /**
     * Set short description
     *
     * @param string $shortDescription
     * @return $this
     */
    public function setShortDescription($shortDescription)
    {
        return $this->setData(self::KEY_SHORT_DESCRIPTION, $shortDescription);
    }

    /**
     * Set discount amount
     *
     * @param float $discountAmount
     * @return $this
     */
    public function setDiscountAmount($discountAmount)
    {
        return $this->setData(self::KEY_DISCOUNT_AMOUNT, $discountAmount);
    }

    /**
     * Set parent code
     *
     * @param string $parentCode
     * @return $this
     */
    public function setParentCode($parentCode)
    {
        return $this->setData(self::KEY_PARENT_CODE, $parentCode);
    }

    /**
     * Set associated item code
     *
     * @param int $associatedItemCode
     * @return $this
     */
    public function setAssociatedItemCode($associatedItemCode)
    {
        return $this->setData(self::KEY_ASSOCIATED_ITEM_CODE, $associatedItemCode);
    }

    /**
     * Set associated item tax class id
     *
     * @param int $taxClassId
     * @return $this
     */
    public function setTaxClassId($taxClassId)
    {
        return $this->setData(self::KEY_TAX_CLASS_ID, $taxClassId);
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
