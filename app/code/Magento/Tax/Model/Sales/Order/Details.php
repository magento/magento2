<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Model\Sales\Order;

/**
 * @codeCoverageIgnore
 * @since 2.0.0
 */
class Details extends \Magento\Framework\Model\AbstractExtensibleModel implements
    \Magento\Tax\Api\Data\OrderTaxDetailsInterface
{
    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */
    const KEY_APPLIED_TAXES = 'applied_taxes';
    const KEY_ITEMS         = 'items';
    /**#@-*/

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getAppliedTaxes()
    {
        return $this->getData(self::KEY_APPLIED_TAXES);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getItems()
    {
        return $this->getData(self::KEY_ITEMS);
    }

    /**
     * Set applied taxes at order level
     *
     * @param \Magento\Tax\Api\Data\OrderTaxDetailsAppliedTaxInterface[] $appliedTaxes
     * @return $this
     * @since 2.0.0
     */
    public function setAppliedTaxes(array $appliedTaxes = null)
    {
        return $this->setData(self::KEY_APPLIED_TAXES, $appliedTaxes);
    }

    /**
     * Set order item tax details
     *
     * @param \Magento\Tax\Api\Data\OrderTaxDetailsItemInterface[] $items
     * @return $this
     * @since 2.0.0
     */
    public function setItems(array $items = null)
    {
        return $this->setData(self::KEY_ITEMS, $items);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Tax\Api\Data\OrderTaxDetailsExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Tax\Api\Data\OrderTaxDetailsExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(\Magento\Tax\Api\Data\OrderTaxDetailsExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
