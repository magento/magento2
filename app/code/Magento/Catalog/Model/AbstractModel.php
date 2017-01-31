<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model;

use Magento\Framework\Api\AttributeValueFactory;

/**
 * Abstract model for catalog entities
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class AbstractModel extends \Magento\Framework\Model\AbstractExtensibleModel
{
    /**
     * Attribute default values
     *
     * This array contain default values for attributes which was redefine
     * value for store
     *
     * @var array|null
     */
    protected $_defaultValues;

    /**
     * This array contains codes of attributes which have value in current store
     *
     * @var array|null
     */
    protected $_storeValuesFlags;

    /**
     * Locked attributes
     *
     * @var array
     */
    protected $_lockedAttributes = [];

    /**
     * Is model deleteable
     *
     * @var boolean
     */
    protected $_isDeleteable = true;

    /**
     * Is model readonly
     *
     * @var boolean
     */
    protected $_isReadonly = false;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Catalog\Model\Attribute\ScopeOverriddenValue
     */
    private $scopeOverriddenValue;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_storeManager = $storeManager;
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
     * Lock attribute
     *
     * @param string $attributeCode
     * @return $this
     */
    public function lockAttribute($attributeCode)
    {
        $this->_lockedAttributes[$attributeCode] = true;
        return $this;
    }

    /**
     * Unlock attribute
     *
     * @param string $attributeCode
     * @return $this
     */
    public function unlockAttribute($attributeCode)
    {
        if ($this->isLockedAttribute($attributeCode)) {
            unset($this->_lockedAttributes[$attributeCode]);
        }

        return $this;
    }

    /**
     * Unlock all attributes
     *
     * @return $this
     */
    public function unlockAttributes()
    {
        $this->_lockedAttributes = [];
        return $this;
    }

    /**
     * Retrieve locked attributes
     *
     * @return array
     */
    public function getLockedAttributes()
    {
        return array_keys($this->_lockedAttributes);
    }

    /**
     * Checks that model have locked attributes
     *
     * @return boolean
     */
    public function hasLockedAttributes()
    {
        return !empty($this->_lockedAttributes);
    }

    /**
     * Retrieve locked attributes
     *
     * @param mixed $attributeCode
     * @return boolean
     */
    public function isLockedAttribute($attributeCode)
    {
        return isset($this->_lockedAttributes[$attributeCode]);
    }

    /**
     * Overwrite data in the object.
     *
     * The $key can be string or array.
     * If $key is string, the attribute value will be overwritten by $value
     *
     * If $key is an array, it will overwrite all the data in the object.
     *
     * $isChanged will specify if the object needs to be saved after an update.
     *
     * @param string|array $key
     * @param mixed $value
     * @return \Magento\Framework\DataObject
     */
    public function setData($key, $value = null)
    {
        if ($this->hasLockedAttributes()) {
            if (is_array($key)) {
                foreach ($this->getLockedAttributes() as $attribute) {
                    if (isset($key[$attribute])) {
                        unset($key[$attribute]);
                    }
                }
            } elseif ($this->isLockedAttribute($key)) {
                return $this;
            }
        } elseif ($this->isReadonly()) {
            return $this;
        }

        return parent::setData($key, $value);
    }

    /**
     * Unset data from the object.
     *
     * The $key can be a string only. Array will be ignored.
     *
     * $isChanged will specify if the object needs to be saved after an update.
     *
     * @param string $key
     * @return $this
     */
    public function unsetData($key = null)
    {
        if ($key !== null && $this->isLockedAttribute($key) || $this->isReadonly()) {
            return $this;
        }

        return parent::unsetData($key);
    }

    /**
     * Get collection instance
     *
     * @return \Magento\Catalog\Model\ResourceModel\Collection\AbstractCollection
     */
    public function getResourceCollection()
    {
        $collection = parent::getResourceCollection()->setStoreId($this->getStoreId());
        return $collection;
    }

    /**
     * Load entity by attribute
     *
     * @param \Magento\Eav\Model\Entity\Attribute\AttributeInterface|integer|string|array $attribute
     * @param null|string|array $value
     * @param string $additionalAttributes
     * @return bool|\Magento\Catalog\Model\AbstractModel
     */
    public function loadByAttribute($attribute, $value, $additionalAttributes = '*')
    {
        $collection = $this->getResourceCollection()->addAttributeToSelect(
            $additionalAttributes
        )->addAttributeToFilter(
            $attribute,
            $value
        )->setPage(
            1,
            1
        );

        foreach ($collection as $object) {
            return $object;
        }
        return false;
    }

    /**
     * Retrieve sore object
     *
     * @return \Magento\Store\Model\Store
     */
    public function getStore()
    {
        return $this->_storeManager->getStore($this->getStoreId());
    }

    /**
     * Retrieve all store ids of object current website
     *
     * @return array
     */
    public function getWebsiteStoreIds()
    {
        return $this->getStore()->getWebsite()->getStoreIds(true);
    }

    /**
     * Adding attribute code and value to default value registry
     *
     * Default value existing is flag for using store value in data
     *
     * @param   string $attributeCode
     * @param   mixed  $value
     * @return  $this
     *
     * @deprecated
     */
    public function setAttributeDefaultValue($attributeCode, $value)
    {
        $this->_defaultValues[$attributeCode] = $value;
        return $this;
    }

    /**
     * Get attribute scope overridden value instance
     *
     * @return \Magento\Catalog\Model\Attribute\ScopeOverriddenValue
     *
     * @deprecated
     */
    private function getAttributeScopeOverriddenValue()
    {
        if ($this->scopeOverriddenValue === null) {
            $this->scopeOverriddenValue = \Magento\Framework\App\ObjectManager::getInstance()
                ->get('Magento\Catalog\Model\Attribute\ScopeOverriddenValue');
        }
        return $this->scopeOverriddenValue;
    }

    /**
     * Retrieve default value for attribute code
     *
     * @param   string $attributeCode
     * @return  array|boolean
     *
     * @deprecated
     */
    public function getAttributeDefaultValue($attributeCode)
    {
        if ($this->_defaultValues === null) {
            $entityType = [
                \Magento\Catalog\Model\Product::ENTITY => \Magento\Catalog\Api\Data\ProductInterface::class,
                \Magento\Catalog\Model\Category::ENTITY => \Magento\Catalog\Api\Data\CategoryInterface::class,
            ][$this->getResource()->getEntityType()->getEntityTypeCode()];
            $this->_defaultValues = $this->getAttributeScopeOverriddenValue()->getDefaultValues($entityType, $this);
        }

        return array_key_exists($attributeCode, $this->_defaultValues) ? $this->_defaultValues[$attributeCode] : false;
    }

    /**
     * Set attribute code flag if attribute has value in current store and does not use
     * value of default store as value
     *
     * @param   string $attributeCode
     * @return  $this
     *
     * @deprecated
     */
    public function setExistsStoreValueFlag($attributeCode)
    {
        $this->_storeValuesFlags[$attributeCode] = true;
        return $this;
    }

    /**
     * Check if object attribute has value in current store
     *
     * @param   string $attributeCode
     * @return  bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     *
     * @deprecated
     */
    public function getExistsStoreValueFlag($attributeCode)
    {
        if ($this->_storeValuesFlags === null) {
            $entityType = [
                \Magento\Catalog\Model\Product::ENTITY => \Magento\Catalog\Api\Data\ProductInterface::class,
                \Magento\Catalog\Model\Category::ENTITY => \Magento\Catalog\Api\Data\CategoryInterface::class,
            ][$this->getResource()->getEntityType()->getEntityTypeCode()];
            return $this->getAttributeScopeOverriddenValue()->containsValue(
                $entityType,
                $this,
                $attributeCode,
                $this->getStore()->getId()
            );
        }

        return array_key_exists($attributeCode, $this->_storeValuesFlags);
    }

    /**
     * Before save unlock attributes
     *
     * @return \Magento\Catalog\Model\AbstractModel
     */
    public function beforeSave()
    {
        $this->unlockAttributes();
        return parent::beforeSave();
    }

    /**
     * Checks model is deletable
     *
     * @return boolean
     */
    public function isDeleteable()
    {
        return $this->_isDeleteable;
    }

    /**
     * Set is deletable flag
     *
     * @param boolean $value
     * @return $this
     */
    public function setIsDeleteable($value)
    {
        $this->_isDeleteable = (bool)$value;
        return $this;
    }

    /**
     * Checks model is deletable
     *
     * @return boolean
     */
    public function isReadonly()
    {
        return $this->_isReadonly;
    }

    /**
     * Set is deletable flag
     *
     * @param boolean $value
     * @return $this
     */
    public function setIsReadonly($value)
    {
        $this->_isReadonly = (bool)$value;
        return $this;
    }
}
