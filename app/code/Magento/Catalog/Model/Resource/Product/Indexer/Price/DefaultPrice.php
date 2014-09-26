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
namespace Magento\Catalog\Model\Resource\Product\Indexer\Price;

/**
 * Default Product Type Price Indexer Resource model
 * For correctly work need define product type id
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class DefaultPrice extends \Magento\Catalog\Model\Resource\Product\Indexer\AbstractIndexer implements PriceInterface
{
    /**
     * Product type code
     *
     * @var string
     */
    protected $_typeId;

    /**
     * Product Type is composite flag
     *
     * @var bool
     */
    protected $_isComposite = false;

    /**
     * Core data
     *
     * @var \Magento\Core\Helper\Data
     */
    protected $_coreData = null;

    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager = null;

    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Core\Helper\Data $coreData
     */
    public function __construct(
        \Magento\Framework\App\Resource $resource,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Core\Helper\Data $coreData
    ) {
        $this->_eventManager = $eventManager;
        $this->_coreData = $coreData;
        parent::__construct($resource, $eavConfig);
    }

    /**
     * Define main price index table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('catalog_product_index_price', 'entity_id');
    }

    /**
     * Set Product Type code
     *
     * @param string $typeCode
     * @return $this
     */
    public function setTypeId($typeCode)
    {
        $this->_typeId = $typeCode;
        return $this;
    }

    /**
     * Retrieve Product Type Code
     *
     * @return string
     * @throws \Magento\Catalog\Exception
     */
    public function getTypeId()
    {
        if (is_null($this->_typeId)) {
            throw new \Magento\Catalog\Exception(__('A product type is not defined for the indexer.'));
        }
        return $this->_typeId;
    }

    /**
     * Set Product Type Composite flag
     *
     * @param bool $flag
     * @return $this
     */
    public function setIsComposite($flag)
    {
        $this->_isComposite = (bool)$flag;
        return $this;
    }

    /**
     * Check product type is composite
     *
     * @return bool
     */
    public function getIsComposite()
    {
        return $this->_isComposite;
    }

    /**
     * Reindex temporary (price result data) for all products
     *
     * @return $this
     * @throws \Exception
     */
    public function reindexAll()
    {
        $this->useIdxTable(true);
        $this->beginTransaction();
        try {
            $this->reindex();
            $this->commit();
        } catch (\Exception $e) {
            $this->rollBack();
            throw $e;
        }
        return $this;
    }

    /**
     * Reindex temporary (price result data) for defined product(s)
     *
     * @param int|array $entityIds
     * @return \Magento\Catalog\Model\Resource\Product\Indexer\Price\DefaultPrice
     */
    public function reindexEntity($entityIds)
    {
        $this->reindex($entityIds);
        return $this;
    }

    /**
     * @param null|int|array $entityIds
     * @return \Magento\Catalog\Model\Resource\Product\Indexer\Price\DefaultPrice
     */
    protected function reindex($entityIds = null)
    {
        if ($this->hasEntity() || !empty($entityIds)) {
            $this->_prepareFinalPriceData($entityIds);
            $this->_applyCustomOption();
            $this->_movePriceDataToIndexTable();
        }
        return $this;
    }

    /**
     * Retrieve final price temporary index table name
     *
     * @see _prepareDefaultFinalPriceTable()
     *
     * @return string
     */
    protected function _getDefaultFinalPriceTable()
    {
        if ($this->useIdxTable()) {
            return $this->getTable('catalog_product_index_price_final_idx');
        }
        return $this->getTable('catalog_product_index_price_final_tmp');
    }

    /**
     * Prepare final price temporary index table
     *
     * @return $this
     */
    protected function _prepareDefaultFinalPriceTable()
    {
        $this->_getWriteAdapter()->delete($this->_getDefaultFinalPriceTable());
        return $this;
    }

    /**
     * Retrieve website current dates table name
     *
     * @return string
     */
    protected function _getWebsiteDateTable()
    {
        return $this->getTable('catalog_product_index_website');
    }

    /**
     * Prepare products default final price in temporary index table
     *
     * @param int|array $entityIds the entity ids limitation
     * @return $this
     */
    protected function _prepareFinalPriceData($entityIds = null)
    {
        $this->_prepareDefaultFinalPriceTable();

        $write = $this->_getWriteAdapter();
        $select = $write->select()->from(
            array('e' => $this->getTable('catalog_product_entity')),
            array('entity_id')
        )->join(
            array('cg' => $this->getTable('customer_group')),
            '',
            array('customer_group_id')
        )->join(
            array('cw' => $this->getTable('store_website')),
            '',
            array('website_id')
        )->join(
            array('cwd' => $this->_getWebsiteDateTable()),
            'cw.website_id = cwd.website_id',
            array()
        )->join(
            array('csg' => $this->getTable('store_group')),
            'csg.website_id = cw.website_id AND cw.default_group_id = csg.group_id',
            array()
        )->join(
            array('cs' => $this->getTable('store')),
            'csg.default_store_id = cs.store_id AND cs.store_id != 0',
            array()
        )->join(
            array('pw' => $this->getTable('catalog_product_website')),
            'pw.product_id = e.entity_id AND pw.website_id = cw.website_id',
            array()
        )->joinLeft(
            array('tp' => $this->_getTierPriceIndexTable()),
            'tp.entity_id = e.entity_id AND tp.website_id = cw.website_id' .
            ' AND tp.customer_group_id = cg.customer_group_id',
            array()
        )->joinLeft(
            array('gp' => $this->_getGroupPriceIndexTable()),
            'gp.entity_id = e.entity_id AND gp.website_id = cw.website_id' .
            ' AND gp.customer_group_id = cg.customer_group_id',
            array()
        )->where(
            'e.type_id = ?',
            $this->getTypeId()
        );

        // add enable products limitation
        $statusCond = $write->quoteInto('=?', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
        $this->_addAttributeToSelect($select, 'status', 'e.entity_id', 'cs.store_id', $statusCond, true);
        if ($this->_coreData->isModuleEnabled('Magento_Tax')) {
            $taxClassId = $this->_addAttributeToSelect($select, 'tax_class_id', 'e.entity_id', 'cs.store_id');
        } else {
            $taxClassId = new \Zend_Db_Expr('0');
        }
        $select->columns(array('tax_class_id' => $taxClassId));

        $price = $this->_addAttributeToSelect($select, 'price', 'e.entity_id', 'cs.store_id');
        $specialPrice = $this->_addAttributeToSelect($select, 'special_price', 'e.entity_id', 'cs.store_id');
        $specialFrom = $this->_addAttributeToSelect($select, 'special_from_date', 'e.entity_id', 'cs.store_id');
        $specialTo = $this->_addAttributeToSelect($select, 'special_to_date', 'e.entity_id', 'cs.store_id');
        $currentDate = $write->getDatePartSql('cwd.website_date');
        $groupPrice = $write->getCheckSql('gp.price IS NULL', "{$price}", 'gp.price');

        $specialFromDate = $write->getDatePartSql($specialFrom);
        $specialToDate = $write->getDatePartSql($specialTo);

        $specialFromUse = $write->getCheckSql("{$specialFromDate} <= {$currentDate}", '1', '0');
        $specialToUse = $write->getCheckSql("{$specialToDate} >= {$currentDate}", '1', '0');
        $specialFromHas = $write->getCheckSql("{$specialFrom} IS NULL", '1', "{$specialFromUse}");
        $specialToHas = $write->getCheckSql("{$specialTo} IS NULL", '1', "{$specialToUse}");
        $finalPrice = $write->getCheckSql(
            "{$specialFromHas} > 0 AND {$specialToHas} > 0" . " AND {$specialPrice} < {$price}",
            $specialPrice,
            $price
        );
        $finalPrice = $write->getCheckSql("{$groupPrice} < {$finalPrice}", $groupPrice, $finalPrice);

        $select->columns(
            array(
                'orig_price' => $price,
                'price' => $finalPrice,
                'min_price' => $finalPrice,
                'max_price' => $finalPrice,
                'tier_price' => new \Zend_Db_Expr('tp.min_price'),
                'base_tier' => new \Zend_Db_Expr('tp.min_price'),
                'group_price' => new \Zend_Db_Expr('gp.price'),
                'base_group_price' => new \Zend_Db_Expr('gp.price')
            )
        );

        if (!is_null($entityIds)) {
            $select->where('e.entity_id IN(?)', $entityIds);
        }

        /**
         * Add additional external limitation
         */
        $this->_eventManager->dispatch(
            'prepare_catalog_product_index_select',
            array(
                'select' => $select,
                'entity_field' => new \Zend_Db_Expr('e.entity_id'),
                'website_field' => new \Zend_Db_Expr('cw.website_id'),
                'store_field' => new \Zend_Db_Expr('cs.store_id')
            )
        );

        $query = $select->insertFromSelect($this->_getDefaultFinalPriceTable(), array(), false);
        $write->query($query);

        /**
         * Add possibility modify prices from external events
         */
        $select = $write->select()->join(
            array('wd' => $this->_getWebsiteDateTable()),
            'i.website_id = wd.website_id',
            array()
        );
        $this->_eventManager->dispatch(
            'prepare_catalog_product_price_index_table',
            array(
                'index_table' => array('i' => $this->_getDefaultFinalPriceTable()),
                'select' => $select,
                'entity_id' => 'i.entity_id',
                'customer_group_id' => 'i.customer_group_id',
                'website_id' => 'i.website_id',
                'website_date' => 'wd.website_date',
                'update_fields' => array('price', 'min_price', 'max_price')
            )
        );

        return $this;
    }

    /**
     * Retrieve table name for custom option temporary aggregation data
     *
     * @return string
     */
    protected function _getCustomOptionAggregateTable()
    {
        if ($this->useIdxTable()) {
            return $this->getTable('catalog_product_index_price_opt_agr_idx');
        }
        return $this->getTable('catalog_product_index_price_opt_agr_tmp');
    }

    /**
     * Retrieve table name for custom option prices data
     *
     * @return string
     */
    protected function _getCustomOptionPriceTable()
    {
        if ($this->useIdxTable()) {
            return $this->getTable('catalog_product_index_price_opt_idx');
        }
        return $this->getTable('catalog_product_index_price_opt_tmp');
    }

    /**
     * Prepare table structure for custom option temporary aggregation data
     *
     * @return $this
     */
    protected function _prepareCustomOptionAggregateTable()
    {
        $this->_getWriteAdapter()->delete($this->_getCustomOptionAggregateTable());
        return $this;
    }

    /**
     * Prepare table structure for custom option prices data
     *
     * @return $this
     */
    protected function _prepareCustomOptionPriceTable()
    {
        $this->_getWriteAdapter()->delete($this->_getCustomOptionPriceTable());
        return $this;
    }

    /**
     * Apply custom option minimal and maximal price to temporary final price index table
     *
     * @return $this
     */
    protected function _applyCustomOption()
    {
        $write = $this->_getWriteAdapter();
        $coaTable = $this->_getCustomOptionAggregateTable();
        $copTable = $this->_getCustomOptionPriceTable();

        $this->_prepareCustomOptionAggregateTable();
        $this->_prepareCustomOptionPriceTable();

        $select = $write->select()->from(
            array('i' => $this->_getDefaultFinalPriceTable()),
            array('entity_id', 'customer_group_id', 'website_id')
        )->join(
            array('cw' => $this->getTable('store_website')),
            'cw.website_id = i.website_id',
            array()
        )->join(
            array('csg' => $this->getTable('store_group')),
            'csg.group_id = cw.default_group_id',
            array()
        )->join(
            array('cs' => $this->getTable('store')),
            'cs.store_id = csg.default_store_id',
            array()
        )->join(
            array('o' => $this->getTable('catalog_product_option')),
            'o.product_id = i.entity_id',
            array('option_id')
        )->join(
            array('ot' => $this->getTable('catalog_product_option_type_value')),
            'ot.option_id = o.option_id',
            array()
        )->join(
            array('otpd' => $this->getTable('catalog_product_option_type_price')),
            'otpd.option_type_id = ot.option_type_id AND otpd.store_id = 0',
            array()
        )->joinLeft(
            array('otps' => $this->getTable('catalog_product_option_type_price')),
            'otps.option_type_id = otpd.option_type_id AND otpd.store_id = cs.store_id',
            array()
        )->group(
            array('i.entity_id', 'i.customer_group_id', 'i.website_id', 'o.option_id')
        );

        $optPriceType = $write->getCheckSql('otps.option_type_price_id > 0', 'otps.price_type', 'otpd.price_type');
        $optPriceValue = $write->getCheckSql('otps.option_type_price_id > 0', 'otps.price', 'otpd.price');
        $minPriceRound = new \Zend_Db_Expr("ROUND(i.price * ({$optPriceValue} / 100), 4)");
        $minPriceExpr = $write->getCheckSql("{$optPriceType} = 'fixed'", $optPriceValue, $minPriceRound);
        $minPriceMin = new \Zend_Db_Expr("MIN({$minPriceExpr})");
        $minPrice = $write->getCheckSql("MIN(o.is_require) = 1", $minPriceMin, '0');

        $tierPriceRound = new \Zend_Db_Expr("ROUND(i.base_tier * ({$optPriceValue} / 100), 4)");
        $tierPriceExpr = $write->getCheckSql("{$optPriceType} = 'fixed'", $optPriceValue, $tierPriceRound);
        $tierPriceMin = new \Zend_Db_Expr("MIN({$tierPriceExpr})");
        $tierPriceValue = $write->getCheckSql("MIN(o.is_require) > 0", $tierPriceMin, 0);
        $tierPrice = $write->getCheckSql("MIN(i.base_tier) IS NOT NULL", $tierPriceValue, "NULL");

        $groupPriceRound = new \Zend_Db_Expr("ROUND(i.base_group_price * ({$optPriceValue} / 100), 4)");
        $groupPriceExpr = $write->getCheckSql("{$optPriceType} = 'fixed'", $optPriceValue, $groupPriceRound);
        $groupPriceMin = new \Zend_Db_Expr("MIN({$groupPriceExpr})");
        $groupPriceValue = $write->getCheckSql("MIN(o.is_require) > 0", $groupPriceMin, 0);
        $groupPrice = $write->getCheckSql("MIN(i.base_group_price) IS NOT NULL", $groupPriceValue, "NULL");

        $maxPriceRound = new \Zend_Db_Expr("ROUND(i.price * ({$optPriceValue} / 100), 4)");
        $maxPriceExpr = $write->getCheckSql("{$optPriceType} = 'fixed'", $optPriceValue, $maxPriceRound);
        $maxPrice = $write->getCheckSql(
            "(MIN(o.type)='radio' OR MIN(o.type)='drop_down')",
            "MAX({$maxPriceExpr})",
            "SUM({$maxPriceExpr})"
        );

        $select->columns(
            array(
                'min_price' => $minPrice,
                'max_price' => $maxPrice,
                'tier_price' => $tierPrice,
                'group_price' => $groupPrice
            )
        );

        $query = $select->insertFromSelect($coaTable);
        $write->query($query);

        $select = $write->select()->from(
            array('i' => $this->_getDefaultFinalPriceTable()),
            array('entity_id', 'customer_group_id', 'website_id')
        )->join(
            array('cw' => $this->getTable('store_website')),
            'cw.website_id = i.website_id',
            array()
        )->join(
            array('csg' => $this->getTable('store_group')),
            'csg.group_id = cw.default_group_id',
            array()
        )->join(
            array('cs' => $this->getTable('store')),
            'cs.store_id = csg.default_store_id',
            array()
        )->join(
            array('o' => $this->getTable('catalog_product_option')),
            'o.product_id = i.entity_id',
            array('option_id')
        )->join(
            array('opd' => $this->getTable('catalog_product_option_price')),
            'opd.option_id = o.option_id AND opd.store_id = 0',
            array()
        )->joinLeft(
            array('ops' => $this->getTable('catalog_product_option_price')),
            'ops.option_id = opd.option_id AND ops.store_id = cs.store_id',
            array()
        );

        $optPriceType = $write->getCheckSql('ops.option_price_id > 0', 'ops.price_type', 'opd.price_type');
        $optPriceValue = $write->getCheckSql('ops.option_price_id > 0', 'ops.price', 'opd.price');

        $minPriceRound = new \Zend_Db_Expr("ROUND(i.price * ({$optPriceValue} / 100), 4)");
        $priceExpr = $write->getCheckSql("{$optPriceType} = 'fixed'", $optPriceValue, $minPriceRound);
        $minPrice = $write->getCheckSql("{$priceExpr} > 0 AND o.is_require > 1", $priceExpr, 0);

        $maxPrice = $priceExpr;

        $tierPriceRound = new \Zend_Db_Expr("ROUND(i.base_tier * ({$optPriceValue} / 100), 4)");
        $tierPriceExpr = $write->getCheckSql("{$optPriceType} = 'fixed'", $optPriceValue, $tierPriceRound);
        $tierPriceValue = $write->getCheckSql("{$tierPriceExpr} > 0 AND o.is_require > 0", $tierPriceExpr, 0);
        $tierPrice = $write->getCheckSql("i.base_tier IS NOT NULL", $tierPriceValue, "NULL");

        $groupPriceRound = new \Zend_Db_Expr("ROUND(i.base_group_price * ({$optPriceValue} / 100), 4)");
        $groupPriceExpr = $write->getCheckSql("{$optPriceType} = 'fixed'", $optPriceValue, $groupPriceRound);
        $groupPriceValue = $write->getCheckSql("{$groupPriceExpr} > 0 AND o.is_require > 0", $groupPriceExpr, 0);
        $groupPrice = $write->getCheckSql("i.base_group_price IS NOT NULL", $groupPriceValue, "NULL");

        $select->columns(
            array(
                'min_price' => $minPrice,
                'max_price' => $maxPrice,
                'tier_price' => $tierPrice,
                'group_price' => $groupPrice
            )
        );

        $query = $select->insertFromSelect($coaTable);
        $write->query($query);

        $select = $write->select()->from(
            array($coaTable),
            array(
                'entity_id',
                'customer_group_id',
                'website_id',
                'min_price' => 'SUM(min_price)',
                'max_price' => 'SUM(max_price)',
                'tier_price' => 'SUM(tier_price)',
                'group_price' => 'SUM(group_price)'
            )
        )->group(
            array('entity_id', 'customer_group_id', 'website_id')
        );
        $query = $select->insertFromSelect($copTable);
        $write->query($query);

        $table = array('i' => $this->_getDefaultFinalPriceTable());
        $select = $write->select()->join(
            array('io' => $copTable),
            'i.entity_id = io.entity_id AND i.customer_group_id = io.customer_group_id' .
            ' AND i.website_id = io.website_id',
            array()
        );
        $select->columns(
            array(
                'min_price' => new \Zend_Db_Expr('i.min_price + io.min_price'),
                'max_price' => new \Zend_Db_Expr('i.max_price + io.max_price'),
                'tier_price' => $write->getCheckSql(
                    'i.tier_price IS NOT NULL',
                    'i.tier_price + io.tier_price',
                    'NULL'
                ),
                'group_price' => $write->getCheckSql(
                    'i.group_price IS NOT NULL',
                    'i.group_price + io.group_price',
                    'NULL'
                )
            )
        );
        $query = $select->crossUpdateFromSelect($table);
        $write->query($query);

        $write->delete($coaTable);
        $write->delete($copTable);

        return $this;
    }

    /**
     * Mode Final Prices index to primary temporary index table
     *
     * @return $this
     */
    protected function _movePriceDataToIndexTable()
    {
        $columns = array(
            'entity_id' => 'entity_id',
            'customer_group_id' => 'customer_group_id',
            'website_id' => 'website_id',
            'tax_class_id' => 'tax_class_id',
            'price' => 'orig_price',
            'final_price' => 'price',
            'min_price' => 'min_price',
            'max_price' => 'max_price',
            'tier_price' => 'tier_price',
            'group_price' => 'group_price'
        );

        $write = $this->_getWriteAdapter();
        $table = $this->_getDefaultFinalPriceTable();
        $select = $write->select()->from($table, $columns);

        $query = $select->insertFromSelect($this->getIdxTable(), array(), false);
        $write->query($query);

        $write->delete($table);

        return $this;
    }

    /**
     * Retrieve table name for product tier price index
     *
     * @return string
     */
    protected function _getTierPriceIndexTable()
    {
        return $this->getTable('catalog_product_index_tier_price');
    }

    /**
     * Retrieve table name for product group price index
     *
     * @return string
     */
    protected function _getGroupPriceIndexTable()
    {
        return $this->getTable('catalog_product_index_group_price');
    }

    /**
     * Retrieve temporary index table name
     *
     * @param string $table
     * @return string
     */
    public function getIdxTable($table = null)
    {
        if ($this->useIdxTable()) {
            return $this->getTable('catalog_product_index_price_idx');
        }
        return $this->getTable('catalog_product_index_price_tmp');
    }

    /**
     * @return bool
     */
    protected function hasEntity()
    {
        $reader = $this->_getReadAdapter();

        $select = $reader->select()->from(
            array($this->getTable('catalog_product_entity')),
            array('count(entity_id)')
        )->where(
            'type_id=?',
            $this->getTypeId()
        );

        return (int)$reader->fetchOne($select) > 0;
    }
}
