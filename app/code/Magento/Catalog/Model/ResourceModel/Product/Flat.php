<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel\Product;

use Magento\Store\Model\Store;
use Magento\Eav\Model\ResourceModel\Attribute\DefaultEntityAttributes\ProviderInterface as DefaultAttributesProvider;

/**
 * Catalog Product Flat resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Flat extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb implements DefaultAttributesProvider
{
    /**
     * Store scope Id
     *
     * @var int
     * @since 2.0.0
     */
    protected $_storeId;

    /**
     * Catalog config
     *
     * @var \Magento\Catalog\Model\Config
     * @since 2.0.0
     */
    protected $_catalogConfig;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    protected $_storeManager;

    /**
     * @var \Magento\Catalog\Model\Product\Attribute\DefaultAttributes
     * @since 2.0.0
     */
    protected $defaultAttributes;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Config $catalogConfig
     * @param \Magento\Catalog\Model\Product\Attribute\DefaultAttributes $defaultAttributes
     * @param string $connectionName
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Config $catalogConfig,
        \Magento\Catalog\Model\Product\Attribute\DefaultAttributes $defaultAttributes,
        $connectionName = null
    ) {
        $this->_storeManager = $storeManager;
        $this->_catalogConfig = $catalogConfig;
        $this->defaultAttributes = $defaultAttributes;
        parent::__construct($context, $connectionName);
    }

    /**
     * Init connection and resource table
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init('catalog_product_flat', 'entity_id');
        $this->setStoreId(null);
    }

    /**
     * Retrieve store for resource model
     *
     * @return int
     * @since 2.0.0
     */
    public function getStoreId()
    {
        return $this->_storeId;
    }

    /**
     * Set store for resource model
     *
     * @param null|string|bool|int|Store $store
     * @return $this
     * @since 2.0.0
     */
    public function setStoreId($store)
    {
        if (is_int($store)) {
            $this->_storeId = $store;
        } else {
            $this->_storeId = $this->_storeManager->getStore()->getId();
        }
        if (empty($this->_storeId)) {
            $defaultStore = $this->_storeManager->getDefaultStoreView();
            if ($defaultStore) {
                $this->_storeId = (int)$defaultStore->getId();
            }
        }
        return $this;
    }

    /**
     * Retrieve Flat Table name
     *
     * @param mixed $store
     * @return string
     * @since 2.0.0
     */
    public function getFlatTableName($store = null)
    {
        if ($store === null) {
            $store = $this->getStoreId();
        }
        return $this->getTable('catalog_product_flat_' . $store);
    }

    /**
     * Retrieve entity type id
     *
     * @return int
     * @since 2.0.0
     */
    public function getTypeId()
    {
        return $this->_catalogConfig->getEntityType(\Magento\Catalog\Model\Product::ENTITY)->getEntityTypeId();
    }

    /**
     * Retrieve attribute columns for collection select
     *
     * @param string $attributeCode
     * @return array|null
     * @since 2.0.0
     */
    public function getAttributeForSelect($attributeCode)
    {
        $describe = $this->getConnection()->describeTable($this->getFlatTableName());
        if (!isset($describe[$attributeCode])) {
            return null;
        }
        $columns = [$attributeCode => $attributeCode];

        $attributeIndex = sprintf('%s_value', $attributeCode);
        if (isset($describe[$attributeIndex])) {
            $columns[$attributeIndex] = $attributeIndex;
        }

        return $columns;
    }

    /**
     * Retrieve Attribute Sort column name
     *
     * @param string $attributeCode
     * @return string
     * @since 2.0.0
     */
    public function getAttributeSortColumn($attributeCode)
    {
        $describe = $this->getConnection()->describeTable($this->getFlatTableName());
        if (!isset($describe[$attributeCode])) {
            return null;
        }
        $attributeIndex = sprintf('%s_value', $attributeCode);
        if (isset($describe[$attributeIndex])) {
            return $attributeIndex;
        }
        return $attributeCode;
    }

    /**
     * Retrieve Flat Table columns list
     *
     * @return array
     * @since 2.0.0
     */
    public function getAllTableColumns()
    {
        $describe = $this->getConnection()->describeTable($this->getFlatTableName());
        return array_keys($describe);
    }

    /**
     * Check whether the attribute is a real field in entity table
     * Rewrited for EAV Collection
     *
     * @param integer|string|\Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute
     * @return bool
     * @since 2.0.0
     */
    public function isAttributeStatic($attribute)
    {
        $attributeCode = null;
        if ($attribute instanceof \Magento\Eav\Model\Entity\Attribute\AttributeInterface) {
            $attributeCode = $attribute->getAttributeCode();
        } elseif (is_string($attribute)) {
            $attributeCode = $attribute;
        } elseif (is_numeric($attribute)) {
            $attributeCode = $this->getAttribute($attribute)->getAttributeCode();
        }

        if ($attributeCode) {
            $columns = $this->getAllTableColumns();
            if (in_array($attributeCode, $columns)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Retrieve entity id field name in entity table
     * Rewrote for EAV collection compatibility
     *
     * @return string
     * @since 2.0.0
     */
    public function getEntityIdField()
    {
        return $this->getIdFieldName();
    }

    /**
     * Retrieve attribute instance
     * Special for non static flat table
     *
     * @param mixed $attribute
     * @return \Magento\Eav\Model\Entity\Attribute\AbstractAttribute
     * @since 2.0.0
     */
    public function getAttribute($attribute)
    {
        return $this->_catalogConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $attribute);
    }

    /**
     * Retrieve main resource table name
     *
     * @return string
     * @since 2.0.0
     */
    public function getMainTable()
    {
        return $this->getFlatTableName($this->getStoreId());
    }

    /**
     * Retrieve default entity static attributes
     *
     * @return string[]
     * @since 2.0.0
     */
    public function getDefaultAttributes()
    {
        return array_unique(
            array_merge(
                $this->defaultAttributes->getDefaultAttributes(),
                [$this->getEntityIdField()]
            )
        );
    }
}
