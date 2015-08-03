<?php
/**
 * Catalog super product attribute resource model
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model\Resource\Product\Type\Configurable;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute as ConfigurableAttribute;

class Attribute extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * Label table name cache
     *
     * @var string
     */
    protected $_labelTable;

    /**
     * Catalog data
     *
     * @var \Magento\Catalog\Helper\Data
     */
    protected $_catalogData = null;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Framework\Model\Resource\Db\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param string|null $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Model\Resource\Db\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Helper\Data $catalogData,
        $resourcePrefix = null
    ) {
        $this->_storeManager = $storeManager;
        $this->_catalogData = $catalogData;
        parent::__construct($context, $resourcePrefix);
    }

    /**
     * Inititalize connection and define tables
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('catalog_product_super_attribute', 'product_super_attribute_id');
        $this->_labelTable = $this->getTable('catalog_product_super_attribute_label');
    }

    /**
     * Retrieve Catalog Helper
     *
     * @return \Magento\Catalog\Helper\Data
     */
    public function getCatalogHelper()
    {
        return $this->_catalogData;
    }

    /**
     * Save Custom labels for Attribute name
     *
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute $attribute
     * @return $this
     */
    public function saveLabel($attribute)
    {
        $adapter = $this->_getWriteAdapter();

        $select = $adapter->select()->from(
            $this->_labelTable,
            'value_id'
        )->where(
            'product_super_attribute_id = :product_super_attribute_id'
        )->where(
            'store_id = :store_id'
        );
        $bind = [
            'product_super_attribute_id' => (int)$attribute->getId(),
            'store_id' => (int)$attribute->getStoreId(),
        ];
        $valueId = $adapter->fetchOne($select, $bind);
        if ($valueId) {
            $adapter->update(
                $this->_labelTable,
                ['use_default' => (int)$attribute->getUseDefault(), 'value' => $attribute->getLabel()],
                $adapter->quoteInto('value_id = ?', (int)$valueId)
            );
        } else {
            $adapter->insert(
                $this->_labelTable,
                [
                    'product_super_attribute_id' => (int)$attribute->getId(),
                    'store_id' => (int)$attribute->getStoreId(),
                    'use_default' => (int)$attribute->getUseDefault(),
                    'value' => $attribute->getLabel()
                ]
            );
        }
        return $this;
    }

    /**
     * Retrieve Used in Configurable Products Attributes
     *
     * @param int $setId The specific attribute set
     * @return array
     */
    public function getUsedAttributes($setId)
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()->distinct(
            true
        )->from(
            ['e' => $this->getTable('catalog_product_entity')],
            null
        )->join(
            ['a' => $this->getMainTable()],
            'e.entity_id = a.product_id',
            ['attribute_id']
        )->where(
            'e.attribute_set_id = :attribute_set_id'
        )->where(
            'e.type_id = :type_id'
        );

        $bind = [
            'attribute_set_id' => $setId,
            'type_id' => \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE,
        ];

        return $adapter->fetchCol($select, $bind);
    }

    /**
     * Get configurable attribute id by product id and attribute id
     *
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute $attribute
     * @param mixed $productId
     * @param mixed $attributeId
     *
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getIdByProductIdAndAttributeId($attribute, $productId, $attributeId)
    {
        $select = $this->_getReadAdapter()->select()->from(
            $this->getMainTable(),
            $this->getIdFieldName()
        )->where(
            'product_id = ?',
            $productId
        )->where(
            'attribute_id = ?',
            $attributeId
        );
        return $this->_getReadAdapter()->fetchOne($select);
    }

    /**
     * Delete configurable attributes by product id
     *
     * @param mixed $productId
     * @return void
     */
    public function deleteAttributesByProductId($productId)
    {
        $select = $this->_getReadAdapter()->select()->from(
            $this->getMainTable(),
            $this->getIdFieldName()
        )->where(
            'product_id = ?',
            $productId
        );
        $this->_getWriteAdapter()->query($this->_getReadAdapter()->deleteFromSelect($select, $this->getMainTable()));
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {
        parent::_afterLoad($object);
        $this->loadLabel($object);
        return $this;
    }

    /**
     * Load label for configurable attribute
     *
     * @param ConfigurableAttribute $object
     * @return $this
     */
    protected function loadLabel(ConfigurableAttribute $object)
    {
        $storeId = (int)$this->_storeManager->getStore()->getId();
        $connection = $this->_getReadAdapter();
        $useDefaultCheck = $connection
            ->getCheckSql('store.use_default IS NULL', 'def.use_default', 'store.use_default');
        $labelCheck = $connection->getCheckSql('store.value IS NULL', 'def.value', 'store.value');
        $select = $connection
            ->select()
            ->from(['def' => $this->_labelTable])
            ->joinLeft(
                ['store' => $this->_labelTable],
                $connection->quoteInto(
                    'store.product_super_attribute_id = def.product_super_attribute_id AND store.store_id = ?',
                    $storeId
                ),
                ['use_default' => $useDefaultCheck, 'label' => $labelCheck]
            )
            ->where('def.product_super_attribute_id = ?', $object->getId())
            ->where('def.store_id = ?', 0);

        $data = $connection->fetchRow($select);
        $object->setLabel($data['label']);
        $object->setUseDefault($data['use_default']);
        return $this;
    }
}
