<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Model;

/**
 * Class Link
 * @codeCoverageIgnore
 * @since 2.0.0
 */
class Link extends \Magento\Framework\Model\AbstractExtensibleModel implements
    \Magento\Bundle\Api\Data\LinkInterface
{
    /**#@+
     * Constants
     */
    const KEY_ID = 'id';
    const KEY_SKU = 'sku';
    const KEY_OPTION_ID = 'option_id';
    const KEY_QTY = 'qty';
    const KEY_POSITION = 'position';
    const KEY_IS_DEFAULT = 'is_default';
    const KEY_PRICE = 'price';
    const KEY_PRICE_TYPE = 'price_type';
    const KEY_CAN_CHANGE_QUANTITY = 'selection_can_change_quantity';
    /**#@-*/

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getId()
    {
        return $this->getData(self::KEY_ID);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setId($id)
    {
        return $this->setData(self::KEY_ID, $id);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getSku()
    {
        return $this->getData(self::KEY_SKU);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getOptionId()
    {
        return $this->getData(self::KEY_OPTION_ID);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getQty()
    {
        return $this->getData(self::KEY_QTY);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getPosition()
    {
        return $this->getData(self::KEY_POSITION);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getIsDefault()
    {
        return $this->getData(self::KEY_IS_DEFAULT);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getPrice()
    {
        return $this->getData(self::KEY_PRICE);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getPriceType()
    {
        return $this->getData(self::KEY_PRICE_TYPE);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getCanChangeQuantity()
    {
        return $this->getData(self::KEY_CAN_CHANGE_QUANTITY);
    }

    /**
     * Set linked product sku
     *
     * @param string $sku
     * @return $this
     * @since 2.0.0
     */
    public function setSku($sku)
    {
        return $this->setData(self::KEY_SKU, $sku);
    }

    /**
     * Set option id
     *
     * @param int $optionId
     * @return $this
     * @since 2.0.0
     */
    public function setOptionId($optionId)
    {
        return $this->setData(self::KEY_OPTION_ID, $optionId);
    }

    /**
     * Set qty
     *
     * @param float $qty
     * @return $this
     * @since 2.0.0
     */
    public function setQty($qty)
    {
        return $this->setData(self::KEY_QTY, $qty);
    }

    /**
     * Set position
     *
     * @param int $position
     * @return $this
     * @since 2.0.0
     */
    public function setPosition($position)
    {
        return $this->setData(self::KEY_POSITION, $position);
    }

    /**
     * Set is default
     *
     * @param bool $isDefault
     * @return $this
     * @since 2.0.0
     */
    public function setIsDefault($isDefault)
    {
        return $this->setData(self::KEY_IS_DEFAULT, $isDefault);
    }

    /**
     * Set price
     *
     * @param float $price
     * @return $this
     * @since 2.0.0
     */
    public function setPrice($price)
    {
        return $this->setData(self::KEY_PRICE, $price);
    }

    /**
     * Set price type
     *
     * @param int $priceType
     * @return $this
     * @since 2.0.0
     */
    public function setPriceType($priceType)
    {
        return $this->setData(self::KEY_PRICE_TYPE, $priceType);
    }

    /**
     * Set whether quantity could be changed
     *
     * @param int $canChangeQuantity
     * @return $this
     * @since 2.0.0
     */
    public function setCanChangeQuantity($canChangeQuantity)
    {
        return $this->setData(self::KEY_CAN_CHANGE_QUANTITY, $canChangeQuantity);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Bundle\Api\Data\LinkExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Bundle\Api\Data\LinkExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(\Magento\Bundle\Api\Data\LinkExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
