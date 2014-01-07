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
 * @category    Magento
 * @package     Magento_Bundle
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Bundle Product Price Index Resource model
 *
 * @category    Magento
 * @package     Magento_Bundle
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Bundle\Model\Resource\Price;

class Index extends \Magento\Core\Model\Resource\Db\AbstractDb
{
    /**
     * EAV attributes cache
     *
     * @var array
     */
    protected $_attributes       = array();

    /**
     * Websites cache
     *
     * @var array
     */
    protected $_websites;

    /**
     * Customer Groups cache
     *
     * @var array
     */
    protected $_customerGroups;

    /**
     * Core event manager proxy
     *
     * @var \Magento\Event\ManagerInterface
     */
    protected $_eventManager = null;

    /**
     * @var \Magento\Catalog\Model\Config
     */
    protected $_config;

    /**
     * @var \Magento\Customer\Model\GroupFactory
     */
    protected $_customerGroup;

    /**
     * @var \Magento\CatalogRule\Model\Resource\RuleFactory
     */
    protected $_catalogRuleFactory;

    /**
     * @var \Magento\Core\Model\LocaleInterface
     */
    protected $_locale;

    /**
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\App\Resource $resource
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Core\Model\LocaleInterface $locale
     * @param \Magento\CatalogRule\Model\Resource\RuleFactory $catalogRuleFactory
     * @param \Magento\Customer\Model\GroupFactory $customerGroup
     * @param \Magento\Catalog\Model\Config $config
     * @param \Magento\Event\ManagerInterface $eventManager
     */
    public function __construct(
        \Magento\App\Resource $resource,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Core\Model\LocaleInterface $locale,
        \Magento\CatalogRule\Model\Resource\RuleFactory $catalogRuleFactory,
        \Magento\Customer\Model\GroupFactory $customerGroup,
        \Magento\Catalog\Model\Config $config,
        \Magento\Event\ManagerInterface $eventManager
    ) {
        $this->_eventManager = $eventManager;
        parent::__construct($resource);
        $this->_config = $config;
        $this->_customerGroup = $customerGroup;
        $this->_catalogRuleFactory = $catalogRuleFactory;
        $this->_locale = $locale;
        $this->_storeManager = $storeManager;
    }

    /**
     * Initialize connection and define main table
     *
     */
    protected function _construct()
    {
        $this->_init('catalog_product_bundle_price_index', 'entity_id');
    }

    /**
     * Retrieve attribute object
     *
     * @param string $attributeCode
     * @return \Magento\Catalog\Model\Resource\Eav\Attribute
     */
    protected function _getAttribute($attributeCode)
    {
        if (!isset($this->_attributes[$attributeCode])) {
            $this->_attributes[$attributeCode] = $this->_config
                ->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $attributeCode);
        }
        return $this->_attributes[$attributeCode];
    }

    /**
     * Retrieve websites collection array
     *
     * @return array
     */
    protected function _getWebsites()
    {
        if (is_null($this->_websites)) {
            $this->_websites = $this->_storeManager->getWebsites(false);
        }
        return $this->_websites;
    }

    /**
     * Retrieve customer groups collection array
     *
     * @return array
     */
    protected function _getCustomerGroups()
    {
        if (is_null($this->_customerGroups)) {
            $this->_customerGroups = array();
            foreach ($this->_customerGroup->create()->getCollection() as $group) {
                $this->_customerGroups[$group->getId()] = $group;
            }
        }
        return $this->_customerGroups;
    }

    /**
     * Retrieve product ids array by product condition
     *
     * @param \Magento\Catalog\Model\Product|\Magento\Catalog\Model\Product\Condition\ConditionInterface|array|int $product
     * @param int $lastEntityId
     * @param int $limit
     * @return array
     */
    public function getProducts($product = null, $lastEntityId = 0, $limit = 100)
    {

        $select = $this->_getReadAdapter()->select()
            ->from(
                array('e' => $this->getTable('catalog_product_entity')),
                array('entity_id')
            )
            ->where('e.type_id=?', \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE);
        if ($product instanceof \Magento\Catalog\Model\Product) {
            $select->where('e.entity_id=?', $product->getId());
        } elseif ($product instanceof \Magento\Catalog\Model\Product\Condition\ConditionInterface) {
            $value = new \Zend_Db_Expr($product->getIdsSelect($this->_getReadAdapter()));
            $select->where('e.entity_id IN(?)', $value);
        } elseif (is_numeric($product) || is_array($product)) {
            $select->where('e.entity_id IN(?)', $product);
        }

        $priceType = $this->_getAttribute('price_type');
        $priceTypeAlias = 't_' . $priceType->getAttributeCode();
        $joinConds = array(
            $priceTypeAlias . '.attribute_id=:attribute_id',
            $priceTypeAlias . '.store_id=0',
            $priceTypeAlias . '.entity_id=e.entity_id'
        );

        $select->joinLeft(
            array($priceTypeAlias => $priceType->getBackend()->getTable()),
            join(' AND ', $joinConds),
            array('price_type' => $priceTypeAlias . '.value')
        );

        $select->where('e.entity_id>:last_entity_id', $lastEntityId)
            ->order('e.entity_id')
            ->limit($limit);
        $bind = array(
            'attribute_id'   => $priceType->getAttributeId(),
            'last_entity_id' => $lastEntityId
        );
        return $this->_getReadAdapter()->fetchPairs($select, $bind);
    }

    /**
     * Reindex Bundle product Price Index
     *
     * @param \Magento\Catalog\Model\Product|\Magento\Catalog\Model\Product\Condition\ConditionInterface|array|int $products
     * @return \Magento\Bundle\Model\Resource\Price\Index
     */
    public function reindex($products = null)
    {
        $lastEntityId = 0;
        while (true) {
            $productsData = $this->getProducts($products, $lastEntityId);
            if (!$productsData) {
                break;
            }

            foreach ($productsData as $productId => $priceType) {
                $this->_reindexProduct($productId, $priceType);
                $lastEntityId = $productId;
            }
        }

        return $this;
    }

    /**
     * Reindex product price
     *
     * @param int $productId
     * @param int $priceType
     * @return \Magento\Bundle\Model\Resource\Price\Index
     */
    protected function _reindexProduct($productId, $priceType)
    {
        $options = $this->getSelections($productId);
        $selectionProducts = array();
        foreach ($options as $option) {
            foreach ($option['selections'] as $selection) {
                $selectionProducts[$selection['product_id']] = $selection['product_id'];
            }
        }

        $priceIndex = array();

        foreach ($this->_getWebsites() as $website) {
            if (!$website->getDefaultStore()) {
                continue;
            }
            $salableStatus = $this->getProductsSalableStatus($selectionProducts, $website);
            $priceData = $this->getProductsPriceData($productId, $website);
            $priceData = $priceData[$productId];

            /* @var $website \Magento\Core\Model\Website */
            foreach ($this->_getCustomerGroups() as $group) {
                /* @var $group \Magento\Customer\Model\Group */
                if ($priceType == \Magento\Bundle\Model\Product\Price::PRICE_TYPE_FIXED) {
                    $basePrice     = $this->_getBasePrice($productId, $priceData, $website, $group);
                    $customOptions = $this->getCustomOptions($productId, $website);
                } elseif ($priceType == \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC) {
                    $basePrice = 0;
                }

                list($minPrice, $maxPrice) = $this->_calculateBundleSelections($options, $salableStatus,
                    $productId, $priceType, $basePrice, $priceData, $priceIndex, $website, $group
                );

                if ($priceType == \Magento\Bundle\Model\Product\Price::PRICE_TYPE_FIXED) {
                    list($minPrice, $maxPrice) =
                        $this->_calculateCustomOptions($customOptions, $basePrice, $minPrice, $maxPrice);
                }

                $this->_savePriceIndex($productId, $website->getId(), $group->getId(), $minPrice, $maxPrice);
            }
        }

        return $this;
    }

    /**
     * Save price index
     *
     * @param int $productId
     * @param int $websiteId
     * @param int $groupId
     * @param float $minPrice
     * @param float $maxPrice
     * @return \Magento\Bundle\Model\Resource\Price\Index
     */
    protected function _savePriceIndex($productId, $websiteId, $groupId, $minPrice, $maxPrice)
    {
        $adapter = $this->_getWriteAdapter();
        $adapter->beginTransaction();
        $bind = array($productId, $websiteId, $groupId, $minPrice, $maxPrice);
        $adapter->insertOnDuplicate($this->getMainTable(), $bind, array('min_price', 'max_price'));
        $adapter->commit();

        return $this;
    }

    /**
     * Retrieve bundle options with selections and prices by product
     *
     * @param int $productId
     * @return array
     */
    public function getSelections($productId)
    {
        $options = array();
        $read = $this->_getReadAdapter();
        $select = $read->select()
            ->from(
                array('option_table' => $this->getTable('catalog_product_bundle_option')),
                array('option_id', 'required', 'type')
            )
            ->join(
                array('selection_table' => $this->getTable('catalog_product_bundle_selection')),
                'selection_table.option_id=option_table.option_id',
                array('selection_id', 'product_id', 'selection_price_type',
                    'selection_price_value', 'selection_qty', 'selection_can_change_qty')
            )
            ->join(
                array('e' => $this->getTable('catalog_product_entity')),
                'e.entity_id=selection_table.product_id AND e.required_options=0',
                array()
            )
            ->where('option_table.parent_id=:product_id');

        $query = $read->query($select, array('product_id' => $productId));
        while ($row = $query->fetch()) {
            if (!isset($options[$row['option_id']])) {
                $options[$row['option_id']] = array(
                    'option_id'     => $row['option_id'],
                    'required'      => $row['required'],
                    'type'          => $row['type'],
                    'selections'    => array()
                );
            }
            $options[$row['option_id']]['selections'][$row['selection_id']] = array(
                'selection_id'      => $row['selection_id'],
                'product_id'        => $row['product_id'],
                'price_type'        => $row['selection_price_type'],
                'price_value'       => $row['selection_price_value'],
                'qty'               => $row['selection_qty'],
                'can_change_qty'    => $row['selection_can_change_qty']
            );
        }

        return $options;
    }

    /**
     * Retrieve salable product statuses
     *
     * @param int|array $products
     * @param \Magento\Core\Model\Website $website
     * @return array
     */
    public function getProductsSalableStatus($products, \Magento\Core\Model\Website $website)
    {
        $read = $this->_getReadAdapter();
        $productsData = array();
        $select = $read->select()
            ->from(array('e' => $this->getTable('catalog_product_entity')), 'entity_id')
            ->where('e.entity_id IN(?)', $products);
        // add belong to website
        $select->joinLeft(
            array('pw' => $this->getTable('catalog_product_website')),
            'e.entity_id=pw.product_id AND pw.website_id=:website_id',
            array('pw.website_id')
        );

        $store = $website->getDefaultStore();

        // add product status
        $status = $this->_getAttribute('status');
        if ($status->isScopeGlobal()) {
            $select->join(
                array('t_status' => $status->getBackend()->getTable()),
                'e.entity_id=t_status.entity_id'
                . ' AND t_status.attribute_id=:status_attribute_id'
                . ' AND t_status.store_id=0',
                array('status' => 't_status.value')
            );
        } else {

            $statusField = $read->getCheckSql(
                't2_status.value_id > 0',
                't2_status.value',
                't1_status.value'
            );

            $statusTable = $status->getBackend()->getTable();
            $select->join(
                array('t1_status' => $statusTable),
                'e.entity_id=t1_status.entity_id'
                . ' AND t1_status.attribute_id=:status_attribute_id'
                . ' AND t1_status.store_id=0',
                array('status' => $statusField)
            )
            ->joinLeft(
                array('t2_status' => $statusTable),
                't1_status.entity_id = t2_status.entity_id'
                . ' AND t1_status.attribute_id = t2_status.attribute_id'
                . ' AND t2_status.store_id=:store_id',
                array()
            );
        }

        $bind = array(
            'status_attribute_id' => $status->getAttributeId(),
            'website_id'   => $website->getId(),
            'store_id'     => $store->getId()
        );

        $this->_eventManager->dispatch('catalog_product_prepare_index_select', array(
            'website'   => $website,
            'select'    => $select,
            'bind'      => $bind
        ));

        $query = $read->query($select, $bind);
        while ($row = $query->fetch()) {
            $salable = isset($row['salable']) ? $row['salable'] : true;
            $website = $row['website_id'] > 0 ? true : false;
            $status  = $row['status'];

            $productsData[$row['entity_id']] = $salable && $status && $website;
        }

        return $productsData;
    }

    /**
     * Retrieve product(s) price data
     *
     * @param int|array $products
     * @param \Magento\Core\Model\Website $website
     * @return array
     */
    public function getProductsPriceData($products, \Magento\Core\Model\Website $website)
    {
        $productsData = array();
        $read = $this->_getReadAdapter();
        $select = $read->select()
            ->from(array('e' => $this->getTable('catalog_product_entity')), 'entity_id')
            ->where('e.entity_id IN(?)', $products);

        $this->_addAttributeDataToSelect($select, 'price', $website);
        $this->_addAttributeDataToSelect($select, 'special_price', $website);
        $this->_addAttributeDataToSelect($select, 'special_from_date', $website);
        $this->_addAttributeDataToSelect($select, 'special_to_date', $website);

        $query = $read->query($select);
        while ($row = $query->fetch()) {
            $productsData[$row['entity_id']] = array(
                'price'             => $row['price'],
                'special_price'     => $row['special_price'],
                'special_from_date' => $row['special_from_date'],
                'special_to_date'   => $row['special_to_date']
            );
        }

        return $productsData;
    }

    /**
     * Add attribute data to select
     *
     * @param \Magento\DB\Select $select
     * @param string $attributeCode
     * @param \Magento\Core\Model\Website $website
     * @return \Magento\Bundle\Model\Resource\Price\Index
     */
    protected function _addAttributeDataToSelect(\Magento\DB\Select $select, $attributeCode,
        \Magento\Core\Model\Website $website)
    {
        $attribute  = $this->_getAttribute($attributeCode);
        $store      = $website->getDefaultStore();
        if ($attribute->isScopeGlobal()) {
            $table = 't_' . $attribute->getAttributeCode();
            $select->joinLeft(
                array($table => $attribute->getBackend()->getTable()),
                "e.entity_id={$table}.entity_id"
                . " AND {$table}.attribute_id={$attribute->getAttributeId()}"
                . " AND {$table}.store_id=0",
                array($attribute->getAttributeCode() => $table . '.value')
            );
        } else {
            $tableName   = $attribute->getBackend()->getTable();
            $tableGlobal = 't1_' . $attribute->getAttributeCode();
            $tableStore  = 't2_' . $attribute->getAttributeCode();

            $attributeCond = $this->getReadConnection()->getCheckSql(
                $tableStore . '.value_id > 0',
                $tableStore . '.value',
                $tableGlobal . '.value'
            );
            $select->joinLeft(
                array($tableGlobal => $tableName),
                "e.entity_id = {$tableGlobal}.entity_id"
                . " AND {$tableGlobal}.attribute_id = {$attribute->getAttributeId()}"
                . " AND {$tableGlobal}.store_id = 0",
                array($attribute->getAttributeCode() => $attributeCond)
            )
            ->joinLeft(
                array($tableStore => $tableName),
                "{$tableGlobal}.entity_id = {$tableStore}.entity_id"
                . " AND {$tableGlobal}.attribute_id = {$tableStore}.attribute_id"
                . " AND {$tableStore}.store_id = " . $store->getId(),
                array()
            );
        }
        return $this;
    }

    /**
     * Retrieve fixed bundle base price (with special price and rules)
     *
     * @param int $productId
     * @param array $priceData
     * @param \Magento\Core\Model\Website $website
     * @param \Magento\Customer\Model\Group $customerGroup
     * @return float
     */
    protected function _getBasePrice($productId, array $priceData, $website, $customerGroup)
    {
        $store          = $website->getDefaultStore();
        $storeTimeStamp = $this->_locale->storeTimeStamp($store);
        $finalPrice     = $this->_calculateSpecialPrice($priceData['price'], $priceData, $website);

        $rulePrice = $this->_catalogRuleFactory->create()
            ->getRulePrice($storeTimeStamp, $website->getId(), $customerGroup->getId(), $productId);

        if ($rulePrice !== null && $rulePrice !== false) {
            $finalPrice = min($finalPrice, $rulePrice);
        }

        return $finalPrice;
    }

    /**
     * Retrieve custom options for product
     *
     * @param int $productId
     * @param \Magento\Core\Model\Website $website
     * @return array
     */
    public function getCustomOptions($productId, \Magento\Core\Model\Website $website)
    {
        $options = array();
        $store   = $website->getDefaultStore();
        $price   = $this->_getAttribute('price');
        $adapter = $this->_getReadAdapter();

        $bind = array(
            ':product_id' => $productId,
            ':store_id'   => $store->getId(),

        );
        $select = $adapter->select()
            ->from(
                array('option_table' => $this->getTable('catalog_product_option')),
                array('option_id', 'is_require', 'type')
            )
            ->where('option_table.product_id=:product_id');

        if ($price->isScopeGlobal()) {
            $select->join(
                array('price_table' => $this->getTable('catalog_product_option_price')),
                'option_table.option_id = price_table.option_id' .
                ' AND price_table.store_id = 0',
                array('value_id' => 'option_price_id', 'price', 'price_type')
            );
        } else {
            $valueIdCond = $adapter->getCheckSql(
                'price_store_table.option_price_id IS NOT NULL',
                'price_store_table.option_price_id',
                'price_global_table.option_price_id'
            );
            $priceCond = $adapter->getCheckSql(
                'price_store_table.price IS NOT NULL',
                'price_store_table.price',
                'price_global_table.price'
            );
            $priceTypeCond = $adapter->getCheckSql(
                'price_store_table.price_type IS NOT NULL',
                'price_store_table.price_type',
                'price_global_table.price_type'
            );

            $select
                ->join(
                    array('price_global_table' => $this->getTable('catalog_product_option_price')),
                    'option_table.option_id=price_global_table.option_id' .
                    ' AND price_global_table.store_id=0',
                    array(
                        'value_id'   => $valueIdCond,
                        'price'      => $priceCond,
                        'price_type' => $priceTypeCond
                    ))
                ->joinLeft(
                    array('price_store_table' => $this->getTable('catalog_product_option_price')),
                    'option_table.option_id = price_store_table.option_id' .
                    ' AND price_store_table.store_id=:store_id',
                    array()
                );
        }

        $query = $adapter->query($select, $bind);
        while ($row = $query->fetch()) {
            if (!isset($options[$row['option_id']])) {
                $options[$row['option_id']] = array(
                    'option_id'     => $row['option_id'],
                    'is_require'    => $row['is_require'],
                    'type'          => $row['type'],
                    'values'        => array()
                );
            }
            $options[$row['option_id']]['values'][$row['value_id']] = array(
                'price_type'        => $row['price_type'],
                'price_value'       => $row['price']
            );
        }

        $select = $adapter->select()
            ->from(
                array('option_table' => $this->getTable('catalog_product_option')),
                array('option_id', 'is_require', 'type')
            )
            ->join(
                array('type_table' => $this->getTable('catalog_product_option_type_value')),
                'option_table.option_id=type_table.option_id',
                array()
            )
            ->where('option_table.product_id=:product_id');

        if ($price->isScopeGlobal()) {
            $select->join(
                array('price_table' => $this->getTable('catalog_product_option_type_price')),
                'type_table.option_type_id=price_table.option_type_id' .
                ' AND price_table.store_id=0',
                array('value_id' => 'option_type_id', 'price', 'price_type')
            );
        } else {
            $select
                ->join(
                    array('price_global_table' => $this->getTable('catalog_product_option_type_price')),
                    'type_table.option_type_id=price_global_table.option_type_id' .
                    ' AND price_global_table.store_id=0',
                    array(
                        'value_id'   => $valueIdCond,
                        'price'      => $priceCond,
                        'price_type' => $priceTypeCond
                    )
                )
                ->joinLeft(
                    array('price_store_table' => $this->getTable('catalog_product_option_type_price')),
                    'type_table.option_type_id=price_store_table.option_type_id' .
                    ' AND price_store_table.store_id=:store_id',
                    array()
                );
        }

        $query = $adapter->query($select, $bind);
        while ($row = $query->fetch()) {
            if (!isset($options[$row['option_id']])) {
                $options[$row['option_id']] = array(
                    'option_id'     => $row['option_id'],
                    'is_require'    => $row['is_require'],
                    'type'          => $row['type'],
                    'values'        => array()
                );
            }
            $options[$row['option_id']]['values'][$row['value_id']] = array(
                'price_type'        => $row['price_type'],
                'price_value'       => $row['price']
            );
        }

        return $options;
    }

    /**
     * Calculate custom options price
     * Return array with indexes(0 -> min_price, 1 -> max_price)
     *
     * @param array $options
     * @param float $basePrice
     * @param float $minPrice
     * @param float $maxPrice
     * @return array
     */
    public function _calculateCustomOptions(array $options, $basePrice, $minPrice, $maxPrice)
    {
        foreach ($options as $option) {
            $optionPrices = array();
            foreach ($option['values'] as $value) {
                if ($value['price_type'] == 'percent') {
                    $valuePrice = $basePrice * $value['price_value'] / 100;
                } else {
                    $valuePrice = $value['price_value'];
                }
                $optionPrices[] = $valuePrice;
            }
            if ($option['is_require']) {
                $minPrice += min($optionPrices);
            }
            $multiTypes = array(
                \Magento\Catalog\Model\Product\Option::OPTION_TYPE_DROP_DOWN,
                \Magento\Catalog\Model\Product\Option::OPTION_TYPE_CHECKBOX,
                \Magento\Catalog\Model\Product\Option::OPTION_TYPE_MULTIPLE
            );
            if ($optionPrices) {
                if (in_array($option['type'], $multiTypes)) {
                    $maxPrice += array_sum($optionPrices);
                } else {
                    $maxPrice += max($optionPrices);
                }
            }
        }

        return array($minPrice, $maxPrice);
    }

    /**
     * Calculate minimal and maximal price for bundle selections
     * Return array with prices (0 -> min_price, 1 -> max_price)
     *
     * @param array $options
     * @param array $salableStatus
     * @param int $productId
     * @param int $priceType
     * @param float $basePrice
     * @param array $priceData
     * @param array $priceIndex
     * @param \Magento\Core\Model\Website $website
     * @param \Magento\Customer\Model\Group $group
     * @return array
     */
    public function _calculateBundleSelections(array $options, array $salableStatus, $productId, $priceType, $basePrice,
        $priceData, $priceIndex, $website, $group)
    {
        $minPrice = $maxPrice = $basePrice;
        $optPrice = 0;

        foreach ($options as $option) {
            $optionPrices = array();
            foreach ($option['selections'] as $selection) {
                if (!$selection['product_id']) {
                    continue;
                }

                if (!$salableStatus[$selection['product_id']]) {
                    continue;
                }

                if ($priceType == \Magento\Bundle\Model\Product\Price::PRICE_TYPE_FIXED) {
                    $basePrice = $this->_getBasePrice($productId, $priceData, $website, $group);
                }

                // calculate selection price
                if ($priceType == \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC) {
                    $priceIndexKey = join('-', array(
                        $selection['product_id'],
                        $website->getId(),
                        $group->getId()
                    ));

                    $selectionPrice = isset($priceIndex[$priceIndexKey]) ? $priceIndex[$priceIndexKey] : 0;
                    $selectionPrice = $this->_calculateSpecialPrice($selectionPrice, $priceData, $website);
                } else {
                    if ($selection['price_type']) { // percent
                        $selectionPrice = $basePrice * $selection['price_value'] / 100;
                    } else {
                        $selectionPrice = $this->_calculateSpecialPrice($selection['price_value'],
                        $priceData, $website);
                    }
                }

                // calculate selection qty
                if ($selection['can_change_qty'] && $option['type'] != 'multi' && $option['type'] != 'checkbox') {
                    $qty = 1;
                } else {
                    $qty = $selection['qty'];
                }

                $selectionPrice = $selectionPrice * $qty;
                $optionPrices[$selection['selection_id']] = $selectionPrice;
            }

            if ($optionPrices) {
                if ($option['required']) {
                    $minPrice += min($optionPrices);
                } else {
                    $optPrice = $optPrice && $optPrice < min($optionPrices) ? $optPrice : min($optionPrices);
                }
                if (in_array($option['type'], array('multi', 'checkbox'))) {
                    $maxPrice += array_sum($optionPrices);
                } else {
                    $maxPrice += max($optionPrices);
                }
            }
        }

        if ($minPrice == 0) {
            $minPrice = $optPrice;
        }
        return array($minPrice, $maxPrice);
    }

    /**
     * Apply special price
     *
     * @param float $finalPrice
     * @param array $priceData
     * @param \Magento\Core\Model\Website $website
     * @return float
     */
    public function _calculateSpecialPrice($finalPrice, array $priceData, \Magento\Core\Model\Website $website)
    {
        $store              = $website->getDefaultStore();
        $specialPrice       = $priceData['special_price'];

        if (!is_null($specialPrice) && $specialPrice != false) {
            if ($this->_locale->isStoreDateInInterval($store, $priceData['special_from_date'],
            $priceData['special_to_date'])) {
                $specialPrice   = ($finalPrice * $specialPrice) / 100;
                $finalPrice     = min($finalPrice, $specialPrice);
            }
        }

        return $finalPrice;
    }

    /**
     * Retrieve price index for products
     *
     * @param int|array $productIds
     * @param int $websiteId
     * @param int $groupId
     * @return array
     */
    public function loadPriceIndex($productIds, $websiteId, $groupId)
    {
        $prices = array();
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
            ->from(
                array('pi' => $this->getMainTable()),
                array('entity_id', 'min_price', 'max_price')
            )
            ->where('entity_id IN(?)', $productIds)
            ->where('website_id=:website_id')
            ->where('customer_group_id=:group_id');
        $bind = array(
            'website_id' => $websiteId,
            'group_id'   => $groupId
        );
        $query = $adapter->query($select, $bind);
        while ($row = $query->fetch()) {
            $prices[$row['entity_id']] = array(
                'min_price' => $row['min_price'],
                'max_price' => $row['max_price']
            );
        }

        return $prices;
    }
}
