<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Model;

class Link extends \Magento\Framework\Model\AbstractExtensibleModel implements
    \Magento\Bundle\Api\Data\LinkInterface
{
    /**
     * {@inheritdoc}
     */
    public function getSku()
    {
        return $this->getData('sku');
    }

    /**
     * {@inheritdoc}
     */
    public function getOptionId()
    {
        return $this->getData('option_id');
    }

    /**
     * {@inheritdoc}
     */
    public function getQty()
    {
        return $this->getData('qty');
    }

    /**
     * {@inheritdoc}
     */
    public function getPosition()
    {
        return $this->getData('position');
    }

    /**
     * {@inheritdoc}
     */
    public function getIsDefined()
    {
        return $this->getData('is_defined');
    }

    /**
     * {@inheritdoc}
     */
    public function getIsDefault()
    {
        return $this->getData('is_default');
    }

    /**
     * {@inheritdoc}
     */
    public function getPrice()
    {
        return $this->getData('price');
    }

    /**
     * {@inheritdoc}
     */
    public function getPriceType()
    {
        return $this->getData('price_type');
    }

    /**
     * {@inheritdoc}
     */
    public function getCanChangeQuantity()
    {
        return $this->getData('can_change_quantity');
    }
}
