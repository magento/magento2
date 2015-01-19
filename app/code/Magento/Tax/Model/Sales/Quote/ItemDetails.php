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
}
