<?php
/**
 * Catalog Configurable Product Attribute Model
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model\Product\Type\Configurable;


/**
 * @method Attribute _getResource()
 * @method Attribute getResource()
 * @method int getProductId()
 * @method Attribute setProductId(int $value)
 * @method Attribute setProductAttribute(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute $value)
 * @method \Magento\Eav\Model\Entity\Attribute\AbstractAttribute getProductAttribute()
 */
class Attribute extends \Magento\Framework\Model\AbstractExtensibleModel implements
    \Magento\ConfigurableProduct\Api\Data\OptionInterface
{
    /**#@+
     * Constants for field names
     */
    const KEY_ATTRIBUTE_ID = 'attribute_id';
    const KEY_LABEL = 'label';
    const KEY_TYPE = 'type';
    const KEY_POSITION = 'position';
    const KEY_IS_USE_DEFAULT = 'is_use_default';
    const KEY_VALUES = 'values';
    /**#@-*/

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\ConfigurableProduct\Model\Resource\Product\Type\Configurable\Attribute');
    }

    /**
     * Add price data to attribute
     *
     * @param array $priceData
     * @return $this
     */
    public function addPrice($priceData)
    {
        $data = $this->getPrices();
        if ($data === null) {
            $data = [];
        }
        $data[] = $priceData;
        $this->setPrices($data);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        if ($this->getData('use_default') && $this->getProductAttribute()) {
            return $this->getProductAttribute()->getStoreLabel();
        } elseif ($this->getData(self::KEY_LABEL) === null && $this->getProductAttribute()) {
            $this->setData(self::KEY_LABEL, $this->getProductAttribute()->getStoreLabel());
        }

        return $this->getData(self::KEY_LABEL);
    }

    /**
     * After save process
     *
     * @return $this
     */
    public function afterSave()
    {
        parent::afterSave();
        $this->_getResource()->saveLabel($this);
        $this->_getResource()->savePrices($this);
        return $this;
    }

    /**
     * Load counfigurable attribute by product and product's attribute
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Catalog\Model\Resource\Eav\Attribute  $attribute
     * @return void
     */
    public function loadByProductAndAttribute($product, $attribute)
    {
        $id = $this->_getResource()->getIdByProductIdAndAttributeId($this, $product->getId(), $attribute->getId());
        if ($id) {
            $this->load($id);
        }
    }

    /**
     * Delete configurable attributes by product id
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return void
     */
    public function deleteByProduct($product)
    {
        $this->_getResource()->deleteAttributesByProductId($product->getId());
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getAttributeId()
    {
        return $this->getData(self::KEY_ATTRIBUTE_ID);
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getType()
    {
        return $this->getData(self::KEY_TYPE);
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getPosition()
    {
        return $this->getData(self::KEY_POSITION);
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getIsUseDefault()
    {
        return $this->getData(self::KEY_IS_USE_DEFAULT);
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getValues()
    {
        return $this->getData(self::KEY_VALUES);
    }

    //@codeCoverageIgnoreStart
    /**
     * @param string $attributeId
     * @return $this
     */
    public function setAttributeId($attributeId)
    {
        return $this->setData(self::KEY_ATTRIBUTE_ID, $attributeId);
    }

    /**
     * @param string $label
     * @return $this
     */
    public function setLabel($label)
    {
        return $this->setData(self::KEY_LABEL, $label);
    }

    /**
     * @param string $type
     * @return $this
     */
    public function setType($type)
    {
        return $this->setData(self::KEY_TYPE, $type);
    }

    /**
     * @param int $position
     * @return $this
     */
    public function setPosition($position)
    {
        return $this->setData(self::KEY_POSITION, $position);
    }

    /**
     * @param bool $isUseDefault
     * @return $this
     */
    public function setIsUseDefault($isUseDefault)
    {
        return $this->setData(self::KEY_IS_USE_DEFAULT, $isUseDefault);
    }

    /**
     * @param \Magento\ConfigurableProduct\Api\Data\OptionValueInterface[] $values
     * @return $this
     */
    public function setValues(array $values = null)
    {
        return $this->setData(self::KEY_VALUES, $values);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\ConfigurableProduct\Api\Data\OptionExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\ConfigurableProduct\Api\Data\OptionExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\ConfigurableProduct\Api\Data\OptionExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
    //@codeCoverageIgnoreEnd
}
