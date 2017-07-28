<?php
/**
 * Catalog Configurable Product Attribute Model
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model\Product\Type\Configurable;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\EntityManager\MetadataPool;

/**
 * @method Attribute _getResource()
 * @method Attribute getResource()
 * @method Attribute setProductAttribute(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute $value)
 * @method \Magento\Eav\Model\Entity\Attribute\AbstractAttribute getProductAttribute()
 * @since 2.0.0
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
     * @var MetadataPool
     * @since 2.1.0
     */
    private $metadataPool;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param MetadataPool $metadataPool
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     * @since 2.1.0
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        MetadataPool $metadataPool,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->metadataPool = $metadataPool;
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Initialize resource model
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init(\Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute::class);
    }

    /**
     * Get attribute options
     *
     * @return array
     * @since 2.0.0
     */
    public function getOptions()
    {
        return $this->getData('options');
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function loadByProductAndAttribute($product, $attribute)
    {
        $metadata = $this->metadataPool->getMetadata(ProductInterface::class);
        $id = $this->_getResource()->getIdByProductIdAndAttributeId(
            $this,
            $product->getData($metadata->getLinkField()),
            $attribute->getId()
        );
        if ($id) {
            $this->load($id);
        }
    }

    /**
     * Delete configurable attributes by product id
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return void
     * @since 2.0.0
     */
    public function deleteByProduct($product)
    {
        $metadata = $this->metadataPool->getMetadata(ProductInterface::class);
        $this->_getResource()->deleteAttributesByProductId($product->getData($metadata->getLinkField()));
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function getAttributeId()
    {
        return $this->getData(self::KEY_ATTRIBUTE_ID);
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function getPosition()
    {
        return $this->getData(self::KEY_POSITION);
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function getIsUseDefault()
    {
        return $this->getData(self::KEY_IS_USE_DEFAULT);
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function getValues()
    {
        return $this->getData(self::KEY_VALUES);
    }

    //@codeCoverageIgnoreStart

    /**
     * @param string $attributeId
     * @return $this
     * @since 2.0.0
     */
    public function setAttributeId($attributeId)
    {
        return $this->setData(self::KEY_ATTRIBUTE_ID, $attributeId);
    }

    /**
     * @param string $label
     * @return $this
     * @since 2.0.0
     */
    public function setLabel($label)
    {
        return $this->setData(self::KEY_LABEL, $label);
    }

    /**
     * @param int $position
     * @return $this
     * @since 2.0.0
     */
    public function setPosition($position)
    {
        return $this->setData(self::KEY_POSITION, $position);
    }

    /**
     * @param bool $isUseDefault
     * @return $this
     * @since 2.0.0
     */
    public function setIsUseDefault($isUseDefault)
    {
        return $this->setData(self::KEY_IS_USE_DEFAULT, $isUseDefault);
    }

    /**
     * @param \Magento\ConfigurableProduct\Api\Data\OptionValueInterface[] $values
     * @return $this
     * @since 2.0.0
     */
    public function setValues(array $values = null)
    {
        return $this->setData(self::KEY_VALUES, $values);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\ConfigurableProduct\Api\Data\OptionExtensionInterface|null
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function setExtensionAttributes(
        \Magento\ConfigurableProduct\Api\Data\OptionExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getProductId()
    {
        return $this->getData(self::KEY_PRODUCT_ID);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setProductId($value)
    {
        return $this->setData(self::KEY_PRODUCT_ID, $value);
    }

    //@codeCoverageIgnoreEnd

    /**
     * @inheritdoc
     * @since 2.1.0
     */
    public function __sleep()
    {
        return array_diff(
            parent::__sleep(),
            ['metadataPool']
        );
    }

    /**
     * @inheritdoc
     * @since 2.1.0
     */
    public function __wakeup()
    {
        parent::__wakeup();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->metadataPool = $objectManager->get(MetadataPool::class);
    }
}
