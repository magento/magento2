<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\Sales\Order\Tax;

/**
 * @codeCoverageIgnore
 */
class Item extends \Magento\Framework\Model\AbstractExtensibleModel implements
    \Magento\Tax\Api\Data\OrderTaxDetailsItemInterface
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('Magento\Tax\Model\Resource\Sales\Order\Tax\Item');
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
    public function getItemId()
    {
        return $this->getData(self::KEY_ITEM_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getAssociatedItemId()
    {
        return $this->getData(self::KEY_ASSOCIATED_ITEM_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getAppliedTaxes()
    {
        return $this->getData(self::KEY_APPLIED_TAXES);
    }
}
