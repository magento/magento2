<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\TaxDetails;

use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Tax\Api\Data\TaxDetailsInterface;

/**
 * @codeCoverageIgnore
 */
class TaxDetails extends AbstractExtensibleModel implements TaxDetailsInterface
{
    /**
     * {@inheritdoc}
     */
    public function getSubtotal()
    {
        return $this->getData(TaxDetailsInterface::KEY_SUBTOTAL);
    }

    /**
     * {@inheritdoc}
     */
    public function getTaxAmount()
    {
        return $this->getData(TaxDetailsInterface::KEY_TAX_AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function getDiscountTaxCompensationAmount()
    {
        return $this->getData(TaxDetailsInterface::KEY_DISCOUNT_TAX_COMPENSATION_AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function getAppliedTaxes()
    {
        return $this->getData(TaxDetailsInterface::KEY_APPLIED_TAXES);
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        return $this->getData(TaxDetailsInterface::KEY_ITEMS);
    }

    /**
     * Set subtotal
     *
     * @param float $subtotal
     * @return $this
     */
    public function setSubtotal($subtotal)
    {
        return $this->setData(TaxDetailsInterface::KEY_SUBTOTAL, $subtotal);
    }

    /**
     * Set tax amount
     *
     * @param float $taxAmount
     * @return $this
     */
    public function setTaxAmount($taxAmount)
    {
        return $this->setData(TaxDetailsInterface::KEY_TAX_AMOUNT, $taxAmount);
    }

    /**
     * Set discount amount
     *
     * @param float $discountTaxCompensationAmount
     * @return $this
     */
    public function setDiscountTaxCompensationAmount($discountTaxCompensationAmount)
    {
        return $this->setData(
            TaxDetailsInterface::KEY_DISCOUNT_TAX_COMPENSATION_AMOUNT,
            $discountTaxCompensationAmount
        );
    }

    /**
     * Set applied taxes
     *
     * @param \Magento\Tax\Api\Data\AppliedTaxInterface[] $appliedTaxes
     * @return $this
     */
    public function setAppliedTaxes(array $appliedTaxes = null)
    {
        return $this->setData(TaxDetailsInterface::KEY_APPLIED_TAXES, $appliedTaxes);
    }

    /**
     * Set TaxDetails items
     *
     * @param \Magento\Tax\Api\Data\TaxDetailsItemInterface[] $items
     * @return $this
     */
    public function setItems(array $items = null)
    {
        return $this->setData(TaxDetailsInterface::KEY_ITEMS, $items);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Tax\Api\Data\TaxDetailsExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Tax\Api\Data\TaxDetailsExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magento\Tax\Api\Data\TaxDetailsExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
