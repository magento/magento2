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
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Construct
     *
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     */
    public function __construct(
        \Magento\Framework\App\Resource $resource,
        \Magento\Framework\StoreManagerInterface $storeManager,
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
        $response->setAdditionalCalculations(array());

        // prepare event arguments
        $eventArgs = array(
            'select' => $select,
            'table' => 'price_index',
            'store_id' => $this->_storeManager->getStore()->getId(),
            'response_object' => $response
        );

        $this->_eventManager->dispatch('catalog_prepare_price_select', $eventArgs);

        return $response;
    }

    /**
     * Prepare search condition for attribute
     *
     * @param \Magento\Catalog\Model\Resource\Eav\Attribute $attribute
     * @param string|array $value
     * @param \Magento\CatalogSearch\Model\Resource\Advanced\Collection $collection
     * @return string|array
     */
    public function prepareCondition($attribute, $value, $collection)
    {
        $condition = false;

        if (is_array($value)) {
            if (!empty($value['from']) || !empty($value['to'])) {
                // range
                $condition = $value;
            } else if ($attribute->getBackendType() == 'varchar') { // multiselect
                // multiselect
                $condition = array('in_set' => $value);
            } else if (!isset($value['from']) && !isset($value['to'])) { // select
                // select
                $condition = array('in' => $value);
            }
        } else {
            if (strlen($value) > 0) {
                if (in_array($attribute->getBackendType(), array('varchar', 'text', 'static'))) {
                    $condition = array('like' => '%' . $value . '%'); // text search
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

        $conditions = array();
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
            array($tableAlias => $table),
            "e.entity_id={$tableAlias}.entity_id " .
            " AND {$tableAlias}.attribute_id={$attribute->getAttributeId()}" .
            " AND {$tableAlias}.store_id={$storeId}",
            array()
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
