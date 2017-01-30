<?php
/**
 * Catalog Configurable Product Attribute Model
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model\Product\Type\Configurable;


/**
 * @method Attribute _getResource()
 * @method Attribute getResource()
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
    const KEY_POSITION = 'position';
    const KEY_IS_USE_DEFAULT = 'is_use_default';
    const KEY_VALUES = 'values';
    const KEY_PRODUCT_ID = 'product_id';
    /**#@-*/

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute');
    }

    /**
     * Get attribute options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->getData('options');
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
        return $this;
    }

    /**
     * Load counfigurable attribute by product and product's attribute
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute  $attribute
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

    /**
     * {@inheritdoc}
     */
    public function getProductId()
    {
        return $this->getData(self::KEY_PRODUCT_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setProductId($value)
    {
        return $this->setData(self::KEY_PRODUCT_ID, $value);
    }
    //@codeCoverageIgnoreEnd
}
