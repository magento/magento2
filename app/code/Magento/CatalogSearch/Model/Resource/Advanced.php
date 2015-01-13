<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Resource;

/**
 * Advanced Catalog Search resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Advanced extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager = null;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Construct
     *
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     */
    public function __construct(
        \Magento\Framework\App\Resource $resource,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Event\ManagerInterface $eventManager
    ) {
        $this->_storeManager = $storeManager;
        $this->_eventManager = $eventManager;
        parent::__construct($resource);
    }

    /**
     * Initialize connection and define catalog product table as main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('catalog_product_entity', 'entity_id');
    }

    /**
     * Prepare response object and dispatch prepare price event
     * Return response object
     *
     * @param \Magento\Framework\DB\Select $select
     * @return \Magento\Framework\Object
     */
    protected function _dispatchPreparePriceEvent($select)
    {
        // prepare response object for event
        $response = new \Magento\Framework\Object();
        $response->setAdditionalCalculations([]);

        // prepare event arguments
        $eventArgs = [
            'select' => $select,
            'table' => 'price_index',
            'store_id' => $this->_storeManager->getStore()->getId(),
            'response_object' => $response,
        ];

        $this->_eventManager->dispatch('catalog_prepare_price_select', $eventArgs);

        return $response;
    }

    /**
     * Prepare search condition for attribute
     *
     * @param \Magento\Catalog\Model\Resource\Eav\Attribute $attribute
     * @param string|array $value
     * @return string|array
     */
    public function prepareCondition($attribute, $value)
    {
        $condition = false;

        if (is_array($value)) {
            if ($attribute->getBackendType() == 'varchar') { // multiselect
                // multiselect
                $condition = ['in_set' => $value];
            } elseif (!isset($value['from']) && !isset($value['to'])) { // select
                // select
                $condition = ['in' => $value];
            } elseif (isset($value['from']) && '' !== $value['from'] || isset($value['to']) && '' !== $value['to']) {
                // range
                $condition = $value;
            }
        } else {
            if (strlen($value) > 0) {
                if (in_array($attribute->getBackendType(), ['varchar', 'text', 'static'])) {
                    $condition = ['like' => '%' . $value . '%']; // text search
                } else {
                    $condition = $value;
                }
            }
        }

        return $condition;
    }

    /**
     * Add filter by attribute rated price
     *
     * @param \Magento\CatalogSearch\Model\Resource\Advanced\Collection $collection
     * @param \Magento\Catalog\Model\Resource\Eav\Attribute $attribute
     * @param string|array $value
     * @param int $rate
     * @return bool
     */
    public function addRatedPriceFilter($collection, $attribute, $value, $rate = 1)
    {
        $adapter = $this->_getReadAdapter();

        $conditions = [];
        if (strlen($value['from']) > 0) {
            $conditions[] = $adapter->quoteInto(
                'price_index.min_price %s * %s >= ?',
                $value['from'],
                \Zend_Db::FLOAT_TYPE
            );
        }
        if (strlen($value['to']) > 0) {
            $conditions[] = $adapter->quoteInto(
                'price_index.min_price %s * %s <= ?',
                $value['to'],
                \Zend_Db::FLOAT_TYPE
            );
        }

        if (!$conditions) {
            return false;
        }

        $collection->addPriceData();
        $select = $collection->getSelect();
        $response = $this->_dispatchPreparePriceEvent($select);
        $additional = join('', $response->getAdditionalCalculations());

        foreach ($conditions as $condition) {
            $select->where(sprintf($condition, $additional, $rate));
        }

        return true;
    }

    /**
     * Add filter by indexable attribute
     *
     * @param \Magento\CatalogSearch\Model\Resource\Advanced\Collection $collection
     * @param \Magento\Catalog\Model\Resource\Eav\Attribute $attribute
     * @param string|array $value
     * @return bool
     */
    public function addIndexableAttributeModifiedFilter($collection, $attribute, $value)
    {
        if ($attribute->getIndexType() == 'decimal') {
            $table = $this->getTable('catalog_product_index_eav_decimal');
        } else {
            $table = $this->getTable('catalog_product_index_eav');
        }

        $tableAlias = 'a_' . $attribute->getAttributeId();
        $storeId = $this->_storeManager->getStore()->getId();
        $select = $collection->getSelect();

        if (is_array($value)) {
            if (isset($value['from']) && isset($value['to'])) {
                if (empty($value['from']) && empty($value['to'])) {
                    return false;
                }
            }
        }

        $select->distinct(true);
        $select->join(
            [$tableAlias => $table],
            "e.entity_id={$tableAlias}.entity_id " .
            " AND {$tableAlias}.attribute_id={$attribute->getAttributeId()}" .
            " AND {$tableAlias}.store_id={$storeId}",
            []
        );

        if (is_array($value) && (isset($value['from']) || isset($value['to']))) {
            if (isset($value['from']) && !empty($value['from'])) {
                $select->where("{$tableAlias}.value >= ?", $value['from']);
            }
            if (isset($value['to']) && !empty($value['to'])) {
                $select->where("{$tableAlias}.value <= ?", $value['to']);
            }
            return true;
        }

        $select->where("{$tableAlias}.value IN(?)", $value);

        return true;
    }
}
