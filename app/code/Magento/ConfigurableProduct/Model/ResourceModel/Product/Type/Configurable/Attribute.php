<?php
/**
 * Catalog super product attribute resource model
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute as ConfigurableAttribute;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute
 *
 * @since 2.0.0
 */
class Attribute extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Label table name cache
     *
     * @var string
     * @since 2.0.0
     */
    protected $_labelTable;

    /**
     * Catalog data
     *
     * @var \Magento\Catalog\Helper\Data
     * @since 2.0.0
     */
    protected $_catalogData = null;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    protected $_storeManager;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param string $connectionName
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Helper\Data $catalogData,
        $connectionName = null
    ) {
        $this->_storeManager = $storeManager;
        $this->_catalogData = $catalogData;
        parent::__construct($context, $connectionName);
    }

    /**
     * Inititalize connection and define tables
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init('catalog_product_super_attribute', 'product_super_attribute_id');
        $this->_labelTable = $this->getTable('catalog_product_super_attribute_label');
    }

    /**
     * Save Custom labels for Attribute name
     *
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute $attribute
     * @return $this
     * @since 2.0.0
     */
    public function saveLabel($attribute)
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from(
            $this->_labelTable,
            'value_id'
        )->where(
            'product_super_attribute_id = :product_super_attribute_id'
        )->where(
            'store_id = :store_id'
        );
        $bind = [
            'product_super_attribute_id' => (int)$attribute->getId(),
            'store_id' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
        ];
        $valueId = $connection->fetchOne($select, $bind);
        if ($valueId) {
            $storeId = (int)$attribute->getStoreId() ?: $this->_storeManager->getStore()->getId();
        } else {
            // if attribute label not exists, always store on default store (0)
            $storeId = Store::DEFAULT_STORE_ID;
        }
        $connection->insertOnDuplicate(
            $this->_labelTable,
            [
                'product_super_attribute_id' => (int)$attribute->getId(),
                'use_default' => (int)$attribute->getUseDefault(),
                'store_id' => $storeId,
                'value' => $attribute->getLabel(),
            ],
            ['value', 'use_default']
        );

        return $this;
    }

    /**
     * Retrieve Used in Configurable Products Attributes
     *
     * @param int $setId The specific attribute set
     * @return array
     * @since 2.0.0
     */
    public function getUsedAttributes($setId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->distinct(
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

        return $connection->fetchCol($select, $bind);
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
     * @since 2.0.0
     */
    public function getIdByProductIdAndAttributeId($attribute, $productId, $attributeId)
    {
        $select = $this->getConnection()->select()->from(
            $this->getMainTable(),
            $this->getIdFieldName()
        )->where(
            'product_id = ?',
            $productId
        )->where(
            'attribute_id = ?',
            $attributeId
        );
        return $this->getConnection()->fetchOne($select);
    }

    /**
     * Delete configurable attributes by product id
     *
     * @param mixed $productId
     * @return void
     * @since 2.0.0
     */
    public function deleteAttributesByProductId($productId)
    {
        $select = $this->getConnection()->select()->from(
            $this->getMainTable(),
            $this->getIdFieldName()
        )->where(
            'product_id = ?',
            $productId
        );
        $this->getConnection()->query($this->getConnection()->deleteFromSelect($select, $this->getMainTable()));
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @since 2.0.0
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
     * @since 2.0.0
     */
    protected function loadLabel(ConfigurableAttribute $object)
    {
        $storeId = (int)$this->_storeManager->getStore()->getId();
        $connection = $this->getConnection();
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
