<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Model\Resource\Product;

use Magento\Store\Model\Store;

/**
 * Catalog Product Flat resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Flat extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * Store scope Id
     *
     * @var int
     */
    protected $_storeId;

    /**
     * Catalog config
     *
     * @var \Magento\Catalog\Model\Config
     */
    protected $_catalogConfig;

    /**
     * Store manager
     *
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Config $catalogConfig
     */
    public function __construct(
        \Magento\Framework\App\Resource $resource,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Config $catalogConfig
    ) {
        $this->_storeManager = $storeManager;
        $this->_catalogConfig = $catalogConfig;
        parent::__construct($resource);
    }

    /**
     * Init connection and resource table
     *
     * @return void
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
     */
    public function setStoreId($store)
    {
        if (is_int($store)) {
            $this->_storeId = $store;
        } else {
            $this->_storeId = $this->_storeManager->getStore()->getId();
        }
        if (empty($this->_storeId)) {
            $this->_storeId = (int)$this->_storeManager->getDefaultStoreView()->getId();
        }
        return $this;
    }

    /**
     * Retrieve Flat Table name
     *
     * @param mixed $store
     * @return string
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
     */
    public function getAttributeForSelect($attributeCode)
    {
        $describe = $this->_getWriteAdapter()->describeTable($this->getFlatTableName());
        if (!isset($describe[$attributeCode])) {
            return null;
        }
        $columns = array($attributeCode => $attributeCode);

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
     */
    public function getAttributeSortColumn($attributeCode)
    {
        $describe = $this->_getWriteAdapter()->describeTable($this->getFlatTableName());
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
     */
    public function getAllTableColumns()
    {
        $describe = $this->_getWriteAdapter()->describeTable($this->getFlatTableName());
        return array_keys($describe);
    }

    /**
     * Check whether the attribute is a real field in entity table
     * Rewrited for EAV Collection
     *
     * @param integer|string|\Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute
     * @return bool
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
     */
    public function getAttribute($attribute)
    {
        return $this->_catalogConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $attribute);
    }

    /**
     * Retrieve main resource table name
     *
     * @return string
     */
    public function getMainTable()
    {
        return $this->getFlatTableName($this->getStoreId());
    }
}
