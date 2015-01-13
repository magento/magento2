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
     * Price table name cache
     *
     * @var string
     */
    protected $_priceTable;

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
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Helper\Data $catalogData
     */
    public function __construct(
        \Magento\Framework\App\Resource $resource,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Helper\Data $catalogData
    ) {
        $this->_storeManager = $storeManager;
        $this->_catalogData = $catalogData;
        parent::__construct($resource);
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
        $this->_priceTable = $this->getTable('catalog_product_super_attribute_pricing');
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
     * Save Options prices (Depends from price save scope)
     *
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute $attribute
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function savePrices($attribute)
    {
        $write = $this->_getWriteAdapter();
        // define website id scope
        if ($this->getCatalogHelper()->isPriceGlobal()) {
            $websiteId = 0;
        } else {
            $websiteId = (int)$this->_storeManager->getStore($attribute->getStoreId())->getWebsite()->getId();
        }

        $values = $attribute->getValues();
        if (!is_array($values)) {
            $values = [];
        }

        $new = [];
        $old = [];

        // retrieve old values
        $select = $write->select()->from(
            $this->_priceTable
        )->where(
            'product_super_attribute_id = :product_super_attribute_id'
        )->where(
            'website_id = :website_id'
        );

        $bind = ['product_super_attribute_id' => (int)$attribute->getId(), 'website_id' => $websiteId];
        $rowSet = $write->fetchAll($select, $bind);
        foreach ($rowSet as $row) {
            $key = implode('-', [$row['website_id'], $row['value_index']]);
            if (!isset($old[$key])) {
                $old[$key] = $row;
            } else {
                // delete invalid (duplicate row)
                $where = $write->quoteInto('value_id = ?', $row['value_id']);
                $write->delete($this->_priceTable, $where);
            }
        }

        // prepare new values
        foreach ($values as $v) {
            if (empty($v['value_index'])) {
                continue;
            }
            $key = implode('-', [$websiteId, $v['value_index']]);
            $new[$key] = [
                'value_index' => $v['value_index'],
                'pricing_value' => $v['pricing_value'],
                'is_percent' => $v['is_percent'],
                'website_id' => $websiteId,
                'use_default' => !empty($v['use_default_value']) ? true : false,
            ];
        }

        $insert = [];
        $update = [];
        $delete = [];

        foreach ($old as $k => $v) {
            if (!isset($new[$k])) {
                $delete[] = $v['value_id'];
            }
        }
        foreach ($new as $k => $v) {
            $needInsert = false;
            $needUpdate = false;
            $needDelete = false;

            $isGlobal = true;
            if (!$this->getCatalogHelper()->isPriceGlobal() && $websiteId != 0) {
                $isGlobal = false;
            }

            $hasValue = $isGlobal && !empty($v['pricing_value']) || !$isGlobal && !$v['use_default'];

            if (isset($old[$k])) {
                // data changed
                $dataChanged = $old[$k]['is_percent'] != $v['is_percent'] ||
                    $old[$k]['pricing_value'] != $v['pricing_value'];
                if (!$hasValue) {
                    $needDelete = true;
                } elseif ($dataChanged) {
                    $needUpdate = true;
                }
            } elseif ($hasValue) {
                $needInsert = true;
            }

            if (!$isGlobal && empty($v['pricing_value'])) {
                $v['pricing_value'] = 0;
                $v['is_percent'] = 0;
            }

            if ($needInsert) {
                $insert[] = [
                    'product_super_attribute_id' => $attribute->getId(),
                    'value_index' => $v['value_index'],
                    'is_percent' => $v['is_percent'],
                    'pricing_value' => $v['pricing_value'],
                    'website_id' => $websiteId,
                ];
            }
            if ($needUpdate) {
                $update[$old[$k]['value_id']] = [
                    'is_percent' => $v['is_percent'],
                    'pricing_value' => $v['pricing_value'],
                ];
            }
            if ($needDelete) {
                $delete[] = $old[$k]['value_id'];
            }
        }

        if (!empty($delete)) {
            $where = $write->quoteInto('value_id IN(?)', $delete);
            $write->delete($this->_priceTable, $where);
        }
        if (!empty($update)) {
            foreach ($update as $valueId => $bind) {
                $where = $write->quoteInto('value_id=?', $valueId);
                $write->update($this->_priceTable, $bind, $where);
            }
        }
        if (!empty($insert)) {
            $write->insertMultiple($this->_priceTable, $insert);
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
        $this->loadPrices($object);
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

    /**
     * Load prices for configurable attribute
     *
     * @param ConfigurableAttribute $object
     * @return $this
     */
    protected function loadPrices(ConfigurableAttribute $object)
    {
        $websiteId = $this->_catalogData->isPriceGlobal() ? 0 : (int)$this->_storeManager->getStore()->getWebsiteId();
        $select = $this->_getReadAdapter()->select()
            ->from($this->_priceTable)
            ->where('product_super_attribute_id = ?', $object->getId())
            ->where('website_id = ?', $websiteId);

        foreach ($select->query() as $row) {
            $data = [
                'value_index'   => $row['value_index'],
                'is_percent'    => $row['is_percent'],
                'pricing_value' => $row['pricing_value'],
            ];
            $object->addPrice($data);
        }
        return $this;
    }
}
