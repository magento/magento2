<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Tax;

/**
 * Sales Order Tax Item model
 * @since 2.0.0
 */
class Item extends \Magento\Framework\Model\AbstractExtensibleModel implements
    \Magento\Tax\Api\Data\OrderTaxDetailsItemInterface
{
    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */
    const KEY_TYPE               = 'type';
    const KEY_ITEM_ID            = 'item_id';
    const KEY_ASSOCIATED_ITEM_ID = 'associated_item_id';
    const KEY_APPLIED_TAXES      = 'applied_taxes';
    /**#@-*/

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init(\Magento\Sales\Model\ResourceModel\Order\Tax\Item::class);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getType()
    {
        return $this->getData(self::KEY_TYPE);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getItemId()
    {
        return $this->getData(self::KEY_ITEM_ID);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getAssociatedItemId()
    {
        return $this->getData(self::KEY_ASSOCIATED_ITEM_ID);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getAppliedTaxes()
    {
        return $this->getData(self::KEY_APPLIED_TAXES);
    }

    /**
     * Set type (shipping, product, weee, gift wrapping, etc)
     *
     * @param string $type
     * @return $this
     * @since 2.0.0
     */
    public function setType($type)
    {
        return $this->setData(self::KEY_TYPE, $type);
    }

    /**
     * Set item id
     *
     * @param int $itemId
     * @return $this
     * @since 2.0.0
     */
    public function setItemId($itemId)
    {
        return $this->setData(self::KEY_ITEM_ID, $itemId);
    }

    /**
     * Set associated item id
     *
     * @param int $associatedItemId
     * @return $this
     * @since 2.0.0
     */
    public function setAssociatedItemId($associatedItemId)
    {
        return $this->setData(self::KEY_ASSOCIATED_ITEM_ID, $associatedItemId);
    }

    /**
     * Set applied taxes
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
     * {@inheritdoc}
     *
     * @return \Magento\Tax\Api\Data\OrderTaxDetailsItemExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Tax\Api\Data\OrderTaxDetailsItemExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(
        \Magento\Tax\Api\Data\OrderTaxDetailsItemExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
