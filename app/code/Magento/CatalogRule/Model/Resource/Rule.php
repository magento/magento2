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


/**
 * Catalog rules resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\CatalogRule\Model\Resource;

use Magento\Catalog\Model\Product;
use Magento\CatalogRule\Model\Rule as ModelRule;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class Rule extends \Magento\Rule\Model\Resource\AbstractResource
{
    /**
     * Store number of seconds in a day
     */
    const SECONDS_IN_DAY = 86400;

    /**
     * @var \Magento\Framework\Logger
     */
    protected $_logger;

    /**
     * Store associated with rule entities information map
     *
     * @var array
     */
    protected $_associatedEntitiesMap = array(
        'website' => array(
            'associations_table' => 'catalogrule_website',
            'rule_id_field' => 'rule_id',
            'entity_id_field' => 'website_id'
        ),
        'customer_group' => array(
            'associations_table' => 'catalogrule_customer_group',
            'rule_id_field' => 'rule_id',
            'entity_id_field' => 'customer_group_id'
        )
    );

    /**
     * Catalog rule data
     *
     * @var \Magento\CatalogRule\Helper\Data
     */
    protected $_catalogRuleData = null;

    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager = null;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $_eavConfig;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_coreDate;

    /**
     * @var \Magento\Catalog\Model\Product\ConditionFactory
     */
    protected $_conditionFactory;

    /**
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param Product\ConditionFactory $conditionFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $coreDate
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\CatalogRule\Helper\Data $catalogRuleData
     * @param \Magento\Framework\Logger $logger
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        \Magento\Framework\App\Resource $resource,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product\ConditionFactory $conditionFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $coreDate,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\CatalogRule\Helper\Data $catalogRuleData,
        \Magento\Framework\Logger $logger,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->_storeManager = $storeManager;
        $this->_conditionFactory = $conditionFactory;
        $this->_coreDate = $coreDate;
        $this->_eavConfig = $eavConfig;
        $this->_eventManager = $eventManager;
        $this->_catalogRuleData = $catalogRuleData;
        $this->_logger = $logger;
        $this->dateTime = $dateTime;
        $this->priceCurrency = $priceCurrency;
        parent::__construct($resource);
    }

    /**
     * Initialize main table and table id field
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('catalogrule', 'rule_id');
    }

    /**
     * Add customer group ids and website ids to rule data after load
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return \Magento\Framework\Model\Resource\Db\AbstractDb
     */
    protected function _afterLoad(AbstractModel $object)
    {
        $object->setData('customer_group_ids', (array)$this->getCustomerGroupIds($object->getId()));
        $object->setData('website_ids', (array)$this->getWebsiteIds($object->getId()));

        return parent::_afterLoad($object);
    }

    /**
     * Bind catalog rule to customer group(s) and website(s).
     * Update products which are matched for rule.
     *
     * @param AbstractModel $object
     * @return $this
     */
    protected function _afterSave(AbstractModel $object)
    {
        if ($object->hasWebsiteIds()) {
            $websiteIds = $object->getWebsiteIds();
            if (!is_array($websiteIds)) {
                $websiteIds = explode(',', (string)$websiteIds);
            }
            $this->bindRuleToEntity($object->getId(), $websiteIds, 'website');
        }

        if ($object->hasCustomerGroupIds()) {
            $customerGroupIds = $object->getCustomerGroupIds();
            if (!is_array($customerGroupIds)) {
                $customerGroupIds = explode(',', (string)$customerGroupIds);
            }
            $this->bindRuleToEntity($object->getId(), $customerGroupIds, 'customer_group');
        }

        parent::_afterSave($object);
        return $this;
    }

    /**
     * Update products which are matched for rule
     *
     * @param ModelRule $rule
     * @return $this
     * @throws \Exception
     */
    public function updateRuleProductData(ModelRule $rule)
    {
        $ruleId = $rule->getId();
        $write = $this->_getWriteAdapter();
        $write->beginTransaction();
        if ($rule->getProductsFilter()) {
            $write->delete(
                $this->getTable('catalogrule_product'),
                array('rule_id=?' => $ruleId, 'product_id IN (?)' => $rule->getProductsFilter())
            );
        } else {
            $write->delete($this->getTable('catalogrule_product'), $write->quoteInto('rule_id=?', $ruleId));
        }

        if (!$rule->getIsActive()) {
            $write->commit();
            return $this;
        }

        $websiteIds = $rule->getWebsiteIds();
        if (!is_array($websiteIds)) {
            $websiteIds = explode(',', $websiteIds);
        }
        if (empty($websiteIds)) {
            return $this;
        }

        \Magento\Framework\Profiler::start('__MATCH_PRODUCTS__');
        $productIds = $rule->getMatchingProductIds();
        \Magento\Framework\Profiler::stop('__MATCH_PRODUCTS__');

        $customerGroupIds = $rule->getCustomerGroupIds();
        $fromTime = strtotime($rule->getFromDate());
        $toTime = strtotime($rule->getToDate());
        $toTime = $toTime ? $toTime + self::SECONDS_IN_DAY - 1 : 0;
        $sortOrder = (int)$rule->getSortOrder();
        $actionOperator = $rule->getSimpleAction();
        $actionAmount = $rule->getDiscountAmount();
        $subActionOperator = $rule->getSubIsEnable() ? $rule->getSubSimpleAction() : '';
        $subActionAmount = $rule->getSubDiscountAmount();
        $actionStop = $rule->getStopRulesProcessing();

        $rows = array();

        try {
            foreach ($productIds as $productId => $validationByWebsite) {
                foreach ($websiteIds as $websiteId) {
                    foreach ($customerGroupIds as $customerGroupId) {
                        if (empty($validationByWebsite[$websiteId])) {
                            continue;
                        }
                        $rows[] = array(
                            'rule_id' => $ruleId,
                            'from_time' => $fromTime,
                            'to_time' => $toTime,
                            'website_id' => $websiteId,
                            'customer_group_id' => $customerGroupId,
                            'product_id' => $productId,
                            'action_operator' => $actionOperator,
                            'action_amount' => $actionAmount,
                            'action_stop' => $actionStop,
                            'sort_order' => $sortOrder,
                            'sub_simple_action' => $subActionOperator,
                            'sub_discount_amount' => $subActionAmount
                        );

                        if (count($rows) == 1000) {
                            $write->insertMultiple($this->getTable('catalogrule_product'), $rows);
                            $rows = array();
                        }
                    }
                }
            }
            if (!empty($rows)) {
                $write->insertMultiple($this->getTable('catalogrule_product'), $rows);
            }

            $write->commit();
        } catch (\Exception $e) {
            $write->rollback();
            throw $e;
        }


        return $this;
    }

    /**
     * Get all product ids matched for rule
     *
     * @param int $ruleId
     * @return array
     */
    public function getRuleProductIds($ruleId)
    {
        $read = $this->_getReadAdapter();
        $select = $read->select()->from(
            $this->getTable('catalogrule_product'),
            'product_id'
        )->where(
            'rule_id=?',
            $ruleId
        );

        return $read->fetchCol($select);
    }

    /**
     * Remove catalog rules product prices for specified date range and product
     *
     * @param int|string $fromDate
     * @param int|string $toDate
     * @param int|null $productId
     * @return $this
     */
    public function removeCatalogPricesForDateRange($fromDate, $toDate, $productId = null)
    {
        $write = $this->_getWriteAdapter();
        $conds = array();
        $cond = $write->quoteInto('rule_date between ?', $this->dateTime->formatDate($fromDate));
        $cond = $write->quoteInto($cond . ' and ?', $this->dateTime->formatDate($toDate));
        $conds[] = $cond;
        if (!is_null($productId)) {
            $conds[] = $write->quoteInto('product_id=?', $productId);
        }

        /**
         * Add information about affected products
         * It can be used in processes which related with product price (like catalog index)
         */
        $select = $this->_getWriteAdapter()->select()->from(
            $this->getTable('catalogrule_product_price'),
            'product_id'
        )->where(
            implode(' AND ', $conds)
        )->group(
            'product_id'
        );

        $replace = $write->insertFromSelect(
            $select,
            $this->getTable('catalogrule_affected_product'),
            array('product_id'),
            true
        );
        $write->query($replace);
        $write->delete($this->getTable('catalogrule_product_price'), $conds);
        return $this;
    }

    /**
     * Delete old price rules data
     *
     * @param string $date
     * @param int|null $productId
     * @return $this
     */
    public function deleteOldData($date, $productId = null)
    {
        $write = $this->_getWriteAdapter();
        $conds = array();
        $conds[] = $write->quoteInto('rule_date<?', $this->dateTime->formatDate($date));
        if (!is_null($productId)) {
            $conds[] = $write->quoteInto('product_id=?', $productId);
        }
        $write->delete($this->getTable('catalogrule_product_price'), $conds);
        return $this;
    }

    /**
     * Get DB resource statement for processing query result
     *
     * @param int $fromDate
     * @param int $toDate
     * @param int|null $productId
     * @param int|null $websiteId
     * @return \Zend_Db_Statement_Interface
     */
    protected function _getRuleProductsStmt($fromDate, $toDate, $productId = null, $websiteId = null)
    {
        $read = $this->_getReadAdapter();
        /**
         * Sort order is important
         * It used for check stop price rule condition.
         * website_id   customer_group_id   product_id  sort_order
         *  1           1                   1           0
         *  1           1                   1           1
         *  1           1                   1           2
         * if row with sort order 1 will have stop flag we should exclude
         * all next rows for same product id from price calculation
         */
        $select = $read->select()->from(
            array('rp' => $this->getTable('catalogrule_product'))
        )->where(
            $read->quoteInto(
                'rp.from_time = 0 or rp.from_time <= ?',
                $toDate
            ) . ' OR ' . $read->quoteInto(
                'rp.to_time = 0 or rp.to_time >= ?',
                $fromDate
            )
        )->order(
            array('rp.website_id', 'rp.customer_group_id', 'rp.product_id', 'rp.sort_order', 'rp.rule_id')
        );

        if (!is_null($productId)) {
            $select->where('rp.product_id=?', $productId);
        }

        /**
         * Join default price and websites prices to result
         */
        $priceAttr = $this->_eavConfig->getAttribute(Product::ENTITY, 'price');
        $priceTable = $priceAttr->getBackend()->getTable();
        $attributeId = $priceAttr->getId();

        $joinCondition = '%1$s.entity_id=rp.product_id AND (%1$s.attribute_id=' .
            $attributeId .
            ') and %1$s.store_id=%2$s';

        $select->join(
            array('pp_default' => $priceTable),
            sprintf($joinCondition, 'pp_default', \Magento\Store\Model\Store::DEFAULT_STORE_ID),
            array('default_price' => 'pp_default.value')
        );

        if ($websiteId !== null) {
            $website = $this->_storeManager->getWebsite($websiteId);
            $defaultGroup = $website->getDefaultGroup();
            if ($defaultGroup instanceof \Magento\Store\Model\Group) {
                $storeId = $defaultGroup->getDefaultStoreId();
            } else {
                $storeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
            }

            $select->joinInner(
                array('product_website' => $this->getTable('catalog_product_website')),
                'product_website.product_id=rp.product_id ' .
                'AND rp.website_id=product_website.website_id ' .
                'AND product_website.website_id=' .
                $websiteId,
                array()
            );

            $tableAlias = 'pp' . $websiteId;
            $fieldAlias = 'website_' . $websiteId . '_price';
            $select->joinLeft(
                array($tableAlias => $priceTable),
                sprintf($joinCondition, $tableAlias, $storeId),
                array($fieldAlias => $tableAlias . '.value')
            );
        } else {
            foreach ($this->_storeManager->getWebsites() as $website) {
                $websiteId = $website->getId();
                $defaultGroup = $website->getDefaultGroup();
                if ($defaultGroup instanceof \Magento\Store\Model\Group) {
                    $storeId = $defaultGroup->getDefaultStoreId();
                } else {
                    $storeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
                }

                $tableAlias = 'pp' . $websiteId;
                $fieldAlias = 'website_' . $websiteId . '_price';
                $select->joinLeft(
                    array($tableAlias => $priceTable),
                    sprintf($joinCondition, $tableAlias, $storeId),
                    array($fieldAlias => $tableAlias . '.value')
                );
            }
        }

        return $read->query($select);
    }

    /**
     * Generate catalog price rules prices for specified date range
     * If from date is not defined - will be used previous day by UTC
     * If to date is not defined - will be used next day by UTC
     *
     * @param int|string|null $fromDate
     * @param int|string|null $toDate
     * @param int $productId
     * @return $this
     * @throws \Exception
     */
    public function applyAllRulesForDateRange($fromDate = null, $toDate = null, $productId = null)
    {
        $write = $this->_getWriteAdapter();
        $write->beginTransaction();

        $this->_eventManager->dispatch('catalogrule_before_apply', array('resource' => $this));

        $clearOldData = false;
        if ($fromDate === null) {
            $fromDate = mktime(0, 0, 0, date('m'), date('d') - 1);
            /**
             * If fromDate not specified we can delete all data oldest than 1 day
             * We have run it for clear table in case when cron was not installed
             * and old data exist in table
             */
            $clearOldData = true;
        }
        if (is_string($fromDate)) {
            $fromDate = strtotime($fromDate);
        }
        if ($toDate === null) {
            $toDate = mktime(0, 0, 0, date('m'), date('d') + 1);
        }
        if (is_string($toDate)) {
            $toDate = strtotime($toDate);
        }

        $product = null;
        if ($productId instanceof Product) {
            $product = $productId;
            $productId = $productId->getId();
        }

        $this->removeCatalogPricesForDateRange($fromDate, $toDate, $productId);
        if ($clearOldData) {
            $this->deleteOldData($fromDate, $productId);
        }

        $dayPrices = array();

        try {
            /**
             * Update products rules prices per each website separately
             * because of max join limit in mysql
             */
            foreach ($this->_storeManager->getWebsites(false) as $website) {
                $productsStmt = $this->_getRuleProductsStmt($fromDate, $toDate, $productId, $website->getId());

                $dayPrices = array();
                $stopFlags = array();
                $prevKey = null;

                while ($ruleData = $productsStmt->fetch()) {
                    $ruleProductId = $ruleData['product_id'];
                    $productKey = $ruleProductId .
                        '_' .
                        $ruleData['website_id'] .
                        '_' .
                        $ruleData['customer_group_id'];

                    if ($prevKey && $prevKey != $productKey) {
                        $stopFlags = array();
                    }

                    /**
                     * Build prices for each day
                     */
                    for ($time = $fromDate; $time <= $toDate; $time += self::SECONDS_IN_DAY) {
                        if (($ruleData['from_time'] == 0 ||
                            $time >= $ruleData['from_time']) && ($ruleData['to_time'] == 0 ||
                            $time <= $ruleData['to_time'])
                        ) {
                            $priceKey = $time . '_' . $productKey;

                            if (isset($stopFlags[$priceKey])) {
                                continue;
                            }

                            if (!isset($dayPrices[$priceKey])) {
                                $dayPrices[$priceKey] = array(
                                    'rule_date' => $time,
                                    'website_id' => $ruleData['website_id'],
                                    'customer_group_id' => $ruleData['customer_group_id'],
                                    'product_id' => $ruleProductId,
                                    'rule_price' => $this->_calcRuleProductPrice($ruleData),
                                    'latest_start_date' => $ruleData['from_time'],
                                    'earliest_end_date' => $ruleData['to_time']
                                );
                            } else {
                                $dayPrices[$priceKey]['rule_price'] = $this->_calcRuleProductPrice(
                                    $ruleData,
                                    $dayPrices[$priceKey]
                                );
                                $dayPrices[$priceKey]['latest_start_date'] = max(
                                    $dayPrices[$priceKey]['latest_start_date'],
                                    $ruleData['from_time']
                                );
                                $dayPrices[$priceKey]['earliest_end_date'] = min(
                                    $dayPrices[$priceKey]['earliest_end_date'],
                                    $ruleData['to_time']
                                );
                            }

                            if ($ruleData['action_stop']) {
                                $stopFlags[$priceKey] = true;
                            }
                        }
                    }

                    $prevKey = $productKey;
                    if (count($dayPrices) > 1000) {
                        $this->_saveRuleProductPrices($dayPrices);
                        $dayPrices = array();
                    }
                }
                $this->_saveRuleProductPrices($dayPrices);
            }
            $this->_saveRuleProductPrices($dayPrices);

            $write->delete($this->getTable('catalogrule_group_website'), array());

            $timestamp = $this->_coreDate->gmtTimestamp();

            $select = $write->select()->distinct(
                true
            )->from(
                $this->getTable('catalogrule_product'),
                array('rule_id', 'customer_group_id', 'website_id')
            )->where(
                "{$timestamp} >= from_time AND (({$timestamp} <= to_time AND to_time > 0) OR to_time = 0)"
            );
            $query = $select->insertFromSelect($this->getTable('catalogrule_group_website'));
            $write->query($query);

            $write->commit();
        } catch (\Exception $e) {
            $this->_logger->logException($e);
            $write->rollback();
            throw $e;
        }

        $productCondition = $this->_conditionFactory->create()->setTable(
            $this->getTable('catalogrule_affected_product')
        )->setPkFieldName(
            'product_id'
        );
        $this->_eventManager->dispatch(
            'catalogrule_after_apply',
            array('product' => $product, 'product_condition' => $productCondition)
        );
        $write->delete($this->getTable('catalogrule_affected_product'));

        return $this;
    }

    /**
     * Calculate product price based on price rule data and previous information
     *
     * @param array $ruleData
     * @param null|array $productData
     * @return float
     */
    protected function _calcRuleProductPrice($ruleData, $productData = null)
    {
        if ($productData !== null && isset($productData['rule_price'])) {
            $productPrice = $productData['rule_price'];
        } else {
            $websiteId = $ruleData['website_id'];
            if (isset($ruleData['website_' . $websiteId . '_price'])) {
                $productPrice = $ruleData['website_' . $websiteId . '_price'];
            } else {
                $productPrice = $ruleData['default_price'];
            }
        }

        $productPrice = $this->_catalogRuleData->calcPriceRule(
            $ruleData['action_operator'],
            $ruleData['action_amount'],
            $productPrice
        );

        return $this->priceCurrency->round($productPrice);
    }

    /**
     * Save rule prices for products to DB
     *
     * @param array $arrData
     * @return $this
     * @throws \Exception
     */
    protected function _saveRuleProductPrices($arrData)
    {
        if (empty($arrData)) {
            return $this;
        }

        $adapter = $this->_getWriteAdapter();
        $productIds = array();

        $adapter->beginTransaction();
        try {
            foreach ($arrData as $key => $data) {
                $productIds['product_id'] = $data['product_id'];
                $arrData[$key]['rule_date'] = $this->dateTime->formatDate($data['rule_date'], false);
                $arrData[$key]['latest_start_date'] = $this->dateTime->formatDate($data['latest_start_date'], false);
                $arrData[$key]['earliest_end_date'] = $this->dateTime->formatDate($data['earliest_end_date'], false);
            }
            $adapter->insertOnDuplicate($this->getTable('catalogrule_affected_product'), array_unique($productIds));
            $adapter->insertOnDuplicate($this->getTable('catalogrule_product_price'), $arrData);
        } catch (\Exception $e) {
            $adapter->rollback();
            throw $e;
        }
        $adapter->commit();

        return $this;
    }

    /**
     * Get catalog rules product price for specific date, website and
     * customer group
     *
     * @param int|string $date
     * @param int $wId
     * @param int $gId
     * @param int $pId
     * @return float|false
     */
    public function getRulePrice($date, $wId, $gId, $pId)
    {
        $data = $this->getRulePrices($date, $wId, $gId, array($pId));
        if (isset($data[$pId])) {
            return $data[$pId];
        }

        return false;
    }

    /**
     * Retrieve product prices by catalog rule for specific date, website and customer group
     * Collect data with  product Id => price pairs
     *
     * @param int|string $date
     * @param int $websiteId
     * @param int $customerGroupId
     * @param array $productIds
     * @return array
     */
    public function getRulePrices($date, $websiteId, $customerGroupId, $productIds)
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()->from(
            $this->getTable('catalogrule_product_price'),
            array('product_id', 'rule_price')
        )->where(
            'rule_date = ?',
            $this->dateTime->formatDate($date, false)
        )->where(
            'website_id = ?',
            $websiteId
        )->where(
            'customer_group_id = ?',
            $customerGroupId
        )->where(
            'product_id IN(?)',
            $productIds
        );
        return $adapter->fetchPairs($select);
    }

    /**
     * Get active rule data based on few filters
     *
     * @param int|string $date
     * @param int $websiteId
     * @param int $customerGroupId
     * @param int $productId
     * @return array
     */
    public function getRulesFromProduct($date, $websiteId, $customerGroupId, $productId)
    {
        $adapter = $this->_getReadAdapter();
        if (is_string($date)) {
            $date = strtotime($date);
        }
        $select = $adapter->select()->from(
            $this->getTable('catalogrule_product')
        )->where(
            'website_id = ?',
            $websiteId
        )->where(
            'customer_group_id = ?',
            $customerGroupId
        )->where(
            'product_id = ?',
            $productId
        )->where(
            'from_time = 0 or from_time < ?',
            $date
        )->where(
            'to_time = 0 or to_time > ?',
            $date
        );

        return $adapter->fetchAll($select);
    }

    /**
     * Retrieve product price data for all customer groups
     *
     * @param int|string $date
     * @param int $wId
     * @param int $pId
     * @return array
     */
    public function getRulesForProduct($date, $wId, $pId)
    {
        $read = $this->_getReadAdapter();
        $select = $read->select()->from(
            $this->getTable('catalogrule_product_price'),
            '*'
        )->where(
            'rule_date=?',
            $this->dateTime->formatDate($date, false)
        )->where(
            'website_id=?',
            $wId
        )->where(
            'product_id=?',
            $pId
        );

        return $read->fetchAll($select);
    }

    /**
     * Apply catalog rule to product
     *
     * @param ModelRule $rule
     * @param Product $product
     * @param array $websiteIds
     * @return $this
     * @throws \Exception
     */
    public function applyToProduct($rule, $product, $websiteIds)
    {
        if (!$rule->getIsActive()) {
            return $this;
        }

        $ruleId = $rule->getId();
        $productId = $product->getId();

        $write = $this->_getWriteAdapter();
        $write->beginTransaction();

        $write->delete(
            $this->getTable('catalogrule_product'),
            array($write->quoteInto('rule_id=?', $ruleId), $write->quoteInto('product_id=?', $productId))
        );

        if (!$rule->getConditions()->validate($product)) {
            $write->delete(
                $this->getTable('catalogrule_product_price'),
                array($write->quoteInto('product_id=?', $productId))
            );
            $write->commit();
            return $this;
        }

        $customerGroupIds = $rule->getCustomerGroupIds();
        $fromTime = strtotime($rule->getFromDate());
        $toTime = strtotime($rule->getToDate());
        $toTime = $toTime ? $toTime + self::SECONDS_IN_DAY - 1 : 0;
        $sortOrder = (int)$rule->getSortOrder();
        $actionOperator = $rule->getSimpleAction();
        $actionAmount = $rule->getDiscountAmount();
        $actionStop = $rule->getStopRulesProcessing();
        $subActionOperator = $rule->getSubIsEnable() ? $rule->getSubSimpleAction() : '';
        $subActionAmount = $rule->getSubDiscountAmount();

        $rows = array();
        try {
            foreach ($websiteIds as $websiteId) {
                foreach ($customerGroupIds as $customerGroupId) {
                    $rows[] = array(
                        'rule_id' => $ruleId,
                        'from_time' => $fromTime,
                        'to_time' => $toTime,
                        'website_id' => $websiteId,
                        'customer_group_id' => $customerGroupId,
                        'product_id' => $productId,
                        'action_operator' => $actionOperator,
                        'action_amount' => $actionAmount,
                        'action_stop' => $actionStop,
                        'sort_order' => $sortOrder,
                        'sub_simple_action' => $subActionOperator,
                        'sub_discount_amount' => $subActionAmount
                    );

                    if (count($rows) == 1000) {
                        $write->insertMultiple($this->getTable('catalogrule_product'), $rows);
                        $rows = array();
                    }
                }
            }

            if (!empty($rows)) {
                $write->insertMultiple($this->getTable('catalogrule_product'), $rows);
            }
        } catch (\Exception $e) {
            $write->rollback();
            throw $e;
        }

        $this->applyAllRulesForDateRange(null, null, $product);

        $write->commit();

        return $this;
    }
}
