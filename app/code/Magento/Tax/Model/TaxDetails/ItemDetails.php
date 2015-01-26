<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\TaxDetails;

use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Tax\Api\Data\TaxDetailsItemInterface;

/**
 * @codeCoverageIgnore
 */
class ItemDetails extends AbstractExtensibleModel implements TaxDetailsItemInterface
{
    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return $this->getData(TaxDetailsItemInterface::KEY_CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->getData(TaxDetailsItemInterface::KEY_TYPE);
    }

    /**
     * {@inheritdoc}
     */
    public function getTaxPercent()
    {
        return $this->getData(TaxDetailsItemInterface::KEY_TAX_PERCENT);
    }

    /**
     * {@inheritdoc}
     */
    public function getPrice()
    {
        return $this->getData(TaxDetailsItemInterface::KEY_PRICE);
    }

    /**
     * {@inheritdoc}
     */
    public function getPriceInclTax()
    {
        return $this->getData(TaxDetailsItemInterface::KEY_PRICE_INCL_TAX);
    }

    /**
     * {@inheritdoc}
     */
    public function getRowTotal()
    {
        return $this->getData(TaxDetailsItemInterface::KEY_ROW_TOTAL);
    }

    /**
     * {@inheritdoc}
     */
    public function getRowTotalInclTax()
    {
        return $this->getData(TaxDetailsItemInterface::KEY_ROW_TOTAL_INCL_TAX);
    }

    /**
     * {@inheritdoc}
     */
    public function getRowTax()
    {
        return $this->getData(TaxDetailsItemInterface::KEY_ROW_TAX);
    }

    /**
     * {@inheritdoc}
     */
    public function getTaxableAmount()
    {
        return $this->getData(TaxDetailsItemInterface::KEY_TAXABLE_AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function getDiscountAmount()
    {
        return $this->getData(TaxDetailsItemInterface::KEY_DISCOUNT_AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function getDiscountTaxCompensationAmount()
    {
        return $this->getData(TaxDetailsItemInterface::KEY_DISCOUNT_TAX_COMPENSATION_AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function getAppliedTaxes()
    {
        return $this->getData(TaxDetailsItemInterface::KEY_APPLIED_TAXES);
    }

    /**
     * {@inheritdoc}
     */
    public function getAssociatedItemCode()
    {
        return $this->getData(TaxDetailsItemInterface::KEY_ASSOCIATED_ITEM_CODE);
    }
}
