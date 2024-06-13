<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\Data\Order\Tax;

class Item extends \Magento\Framework\Model\AbstractExtensibleModel implements
    \Magento\Tax\Api\Data\OrderTaxItemInterface
{
    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(\Magento\Sales\Model\ResourceModel\Order\Tax\Item::class);
    }

    /**
     * @inheritDoc
     */
    public function getTaxItemId()
    {
        return $this->getData(self::TAX_ITEM_ID);
    }

    /**
     * @inheritDoc
     */
    public function setTaxItemId($taxItemId)
    {
        return $this->setData(self::TAX_ITEM_ID, $taxItemId);
    }

    /**
     * @inheritDoc
     */
    public function getTaxId()
    {
        return $this->getData(self::TAX_ID);
    }

    /**
     * @inheritDoc
     */
    public function setTaxId($taxId)
    {
        return $this->setData(self::TAX_ID, $taxId);
    }

    /**
     * @inheritDoc
     */
    public function getItemId()
    {
        return $this->getData(self::ITEM_ID);
    }

    /**
     * @inheritDoc
     */
    public function setItemId($itemId)
    {
        return $this->setData(self::ITEM_ID, $itemId);
    }

    /**
     * @inheritDoc
     */
    public function getTaxCode()
    {
        return $this->getData(self::TAX_CODE);
    }

    /**
     * @inheritDoc
     */
    public function setTaxCode($taxCode)
    {
        return $this->setData(self::TAX_CODE, $taxCode);
    }

    /**
     * @inheritDoc
     */
    public function getTaxPercent()
    {
        return $this->getData(self::TAX_PERCENT);
    }

    /**
     * @inheritDoc
     */
    public function setTaxPercent($taxPercent)
    {
        return $this->setData(self::TAX_PERCENT, $taxPercent);
    }

    /**
     * @inheritDoc
     */
    public function getAmount()
    {
        return $this->getData(self::AMOUNT);
    }

    /**
     * @inheritDoc
     */
    public function setAmount($amount)
    {
        return $this->setData(self::AMOUNT, $amount);
    }

    /**
     * @inheritDoc
     */
    public function getBaseAmount()
    {
        return $this->getData(self::BASE_AMOUNT);
    }

    /**
     * @inheritDoc
     */
    public function setBaseAmount($baseAmount)
    {
        return $this->setData(self::BASE_AMOUNT, $baseAmount);
    }

    /**
     * @inheritDoc
     */
    public function getRealAmount()
    {
        return $this->getData(self::REAL_AMOUNT);
    }

    /**
     * @inheritDoc
     */
    public function setRealAmount($realAmount)
    {
        return $this->setData(self::REAL_AMOUNT, $realAmount);
    }

    /**
     * @inheritDoc
     */
    public function getRealBaseAmount()
    {
        return $this->getData(self::REAL_BASE_AMOUNT);
    }

    /**
     * @inheritDoc
     */
    public function setRealBaseAmount($realBaseAmount)
    {
        return $this->setData(self::REAL_BASE_AMOUNT, $realBaseAmount);
    }

    /**
     * @inheritDoc
     */
    public function getAssociatedItemId()
    {
        return $this->getData(self::ASSOCIATED_ITEM_ID);
    }

    /**
     * @inheritDoc
     */
    public function setAssociatedItemId($associatedItemId)
    {
        return $this->setData(self::ASSOCIATED_ITEM_ID, $associatedItemId);
    }

    /**
     * @inheritDoc
     */
    public function getTaxableItemType()
    {
        return $this->getData(self::TAXABLE_ITEM_TYPE);
    }

    /**
     * @inheritDoc
     */
    public function setTaxableItemType($taxableItemType)
    {
        return $this->setData(self::TAXABLE_ITEM_TYPE, $taxableItemType);
    }

    /**
     * @inheritDoc
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * @inheritDoc
     */
    public function setExtensionAttributes(\Magento\Tax\Api\Data\OrderTaxItemExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
