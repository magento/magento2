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
}
