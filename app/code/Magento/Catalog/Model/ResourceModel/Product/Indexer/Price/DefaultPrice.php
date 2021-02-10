<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel\Product\Indexer\Price;

use Magento\Catalog\Model\ResourceModel\Product\Indexer\AbstractIndexer;
use Magento\Framework\Indexer\DimensionalIndexerInterface;

/**
 * Default Product Type Price Indexer Resource model
 *
 * For correctly work need define product type id
 *
 * @api
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 * @deprecated 102.0.6 Not used anymore for price indexation. Class left for backward compatibility
 * @see DimensionalIndexerInterface
 */
class DefaultPrice extends AbstractIndexer implements PriceInterface
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
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager = null;

    /**
     * @var bool|null
     */
    private $hasEntity = null;

    /**
     * @var IndexTableStructureFactory
     */
    private $indexTableStructureFactory;

    /**
     * @var PriceModifierInterface[]
     */
    private $priceModifiers = [];

    /**
     * DefaultPrice constructor.
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Indexer\Table\StrategyInterface $tableStrategy
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param string|null $connectionName
     * @param IndexTableStructureFactory $indexTableStructureFactory
     * @param PriceModifierInterface[] $priceModifiers
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Indexer\Table\StrategyInterface $tableStrategy,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Module\Manager $moduleManager,
        $connectionName = null,
        IndexTableStructureFactory $indexTableStructureFactory = null,
        array $priceModifiers = []
    ) {
        $this->_eventManager = $eventManager;
        $this->moduleManager = $moduleManager;
        parent::__construct($context, $tableStrategy, $eavConfig, $connectionName);

        $this->indexTableStructureFactory = $indexTableStructureFactory ?:
            \Magento\Framework\App\ObjectManager::getInstance()->get(IndexTableStructureFactory::class);
        foreach ($priceModifiers as $priceModifier) {
            if (!($priceModifier instanceof PriceModifierInterface)) {
                throw new \InvalidArgumentException(
                    'Argument \'priceModifiers\' must be of the type ' . PriceModifierInterface::class . '[]'
                );
            }

            $this->priceModifiers[] = $priceModifier;
        }
    }

    /**
     * Get Table strategy
     *
     * @return \Magento\Framework\Indexer\Table\StrategyInterface
     */
    public function getTableStrategy()
    {
        return $this->tableStrategy;
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
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getTypeId()
    {
        if ($this->_typeId === null) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('A product type is not defined for the indexer.')
            );
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
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
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
        $this->tableStrategy->setUseIdxTable(true);
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
     * @return \Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\DefaultPrice
     */
    public function reindexEntity($entityIds)
    {
        $this->reindex($entityIds);
        return $this;
    }

    /**
     * Reindex prices.
     *
     * @param null|int|array $entityIds
     * @return \Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\DefaultPrice
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
        return $this->tableStrategy->getTableName('catalog_product_index_price_final');
    }

    /**
     * Prepare final price temporary index table
     *
     * @return $this
     * @deprecated 102.0.5
     * @see prepareFinalPriceTable()
     */
    protected function _prepareDefaultFinalPriceTable()
    {
        $this->getConnection()->delete($this->_getDefaultFinalPriceTable());
        return $this;
    }

    /**
     * Create (if needed), clean and return structure of final price table
     *
     * @return IndexTableStructure
     */
    private function prepareFinalPriceTable()
    {
        $tableName = $this->_getDefaultFinalPriceTable();
        $this->getConnection()->delete($tableName);

        $finalPriceTable = $this->indexTableStructureFactory->create(
            [
            'tableName' => $tableName,
            'entityField' => 'entity_id',
            'customerGroupField' => 'customer_group_id',
            'websiteField' => 'website_id',
            'taxClassField' => 'tax_class_id',
            'originalPriceField' => 'orig_price',
            'finalPriceField' => 'price',
            'minPriceField' => 'min_price',
            'maxPriceField' => 'max_price',
            'tierPriceField' => 'tier_price',
            ]
        );

        return $finalPriceTable;
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
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareFinalPriceData($entityIds = null)
    {
        return $this->prepareFinalPriceDataForType($entityIds, $this->getTypeId());
    }

    /**
     * Prepare products default final price in temporary index table
     *
     * @param int|array $entityIds the entity ids limitation
     * @param string|null $type product type, all if null
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function prepareFinalPriceDataForType($entityIds, $type)
    {
        $finalPriceTable = $this->prepareFinalPriceTable();

        $select = $this->getSelect($entityIds, $type);
        $query = $select->insertFromSelect($finalPriceTable->getTableName(), [], false);
        $this->getConnection()->query($query);

        $this->modifyPriceIndex($finalPriceTable);

        return $this;
    }

    /**
     * Forms Select for collecting price related data for final price index table
     * Next types of prices took into account: default, special, tier price
     * Moved to protected for possible reusing
     *
     * @param int|array $entityIds Ids for filtering output result
     * @param string|null $type Type for filtering output result by specified product type (all if null)
     * @return \Magento\Framework\DB\Select
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @since 101.0.8
     */
    protected function getSelect($entityIds = null, $type = null)
    {
        $metadata = $this->getMetadataPool()->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class);
        $linkField = $metadata->getLinkField();
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            ['e' => $this->getTable('catalog_product_entity')],
            ['entity_id']
        )->join(
            ['cg' => $this->getTable('customer_group')],
            '',
            ['customer_group_id']
        )->join(
            ['cw' => $this->getTable('store_website')],
            '',
            ['website_id']
        )->join(
            ['cwd' => $this->_getWebsiteDateTable()],
            'cw.website_id = cwd.website_id',
            []
        )->join(
            ['csg' => $this->getTable('store_group')],
            'csg.website_id = cw.website_id AND cw.default_group_id = csg.group_id',
            []
        )->join(
            ['cs' => $this->getTable('store')],
            'csg.default_store_id = cs.store_id AND cs.store_id != 0',
            []
        )->join(
            ['pw' => $this->getTable('catalog_product_website')],
            'pw.product_id = e.entity_id AND pw.website_id = cw.website_id',
            []
        )->joinLeft(
            // we need this only for BCC in case someone expects table `tp` to be present in query
            ['tp' => $this->getTable('catalog_product_index_tier_price')],
            'tp.entity_id = e.entity_id AND tp.customer_group_id = cg.customer_group_id' .
            ' AND tp.website_id = pw.website_id',
            []
        )->joinLeft(
            // calculate tier price specified as Website = `All Websites` and Customer Group = `Specific Customer Group`
            ['tier_price_1' => $this->getTable('catalog_product_entity_tier_price')],
            'tier_price_1.' . $linkField . ' = e.' . $linkField . ' AND tier_price_1.all_groups = 0' .
            ' AND tier_price_1.customer_group_id = cg.customer_group_id AND tier_price_1.qty = 1' .
            ' AND tier_price_1.website_id = 0',
            []
        )->joinLeft(
            // calculate tier price specified as Website = `Specific Website`
            //and Customer Group = `Specific Customer Group`
            ['tier_price_2' => $this->getTable('catalog_product_entity_tier_price')],
            'tier_price_2.' . $linkField . ' = e.' . $linkField . ' AND tier_price_2.all_groups = 0' .
            ' AND tier_price_2.customer_group_id = cg.customer_group_id AND tier_price_2.qty = 1' .
            ' AND tier_price_2.website_id = cw.website_id',
            []
        )->joinLeft(
            // calculate tier price specified as Website = `All Websites` and Customer Group = `ALL GROUPS`
            ['tier_price_3' => $this->getTable('catalog_product_entity_tier_price')],
            'tier_price_3.' . $linkField . ' = e.' . $linkField . ' AND tier_price_3.all_groups = 1' .
            ' AND tier_price_3.customer_group_id = 0 AND tier_price_3.qty = 1 AND tier_price_3.website_id = 0',
            []
        )->joinLeft(
            // calculate tier price specified as Website = `Specific Website` and Customer Group = `ALL GROUPS`
            ['tier_price_4' => $this->getTable('catalog_product_entity_tier_price')],
            'tier_price_4.' . $linkField . ' = e.' . $linkField . ' AND tier_price_4.all_groups = 1' .
            ' AND tier_price_4.customer_group_id = 0 AND tier_price_4.qty = 1' .
            ' AND tier_price_4.website_id = cw.website_id',
            []
        );

        if ($type !== null) {
            $select->where('e.type_id = ?', $type);
        }

        // add enable products limitation
        $statusCond = $connection->quoteInto(
            '=?',
            \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
        );
        $this->_addAttributeToSelect(
            $select,
            'status',
            'e.' . $linkField,
            'cs.store_id',
            $statusCond,
            true
        );
        if ($this->moduleManager->isEnabled('Magento_Tax')) {
            $taxClassId = $this->_addAttributeToSelect(
                $select,
                'tax_class_id',
                'e.' . $linkField,
                'cs.store_id'
            );
        } else {
            $taxClassId = new \Zend_Db_Expr('0');
        }
        $select->columns(['tax_class_id' => $taxClassId]);

        $price = $this->_addAttributeToSelect(
            $select,
            'price',
            'e.' . $linkField,
            'cs.store_id'
        );
        $specialPrice = $this->_addAttributeToSelect(
            $select,
            'special_price',
            'e.' . $linkField,
            'cs.store_id'
        );
        $specialFrom = $this->_addAttributeToSelect(
            $select,
            'special_from_date',
            'e.' . $linkField,
            'cs.store_id'
        );
        $specialTo = $this->_addAttributeToSelect(
            $select,
            'special_to_date',
            'e.' . $linkField,
            'cs.store_id'
        );
        $currentDate = 'cwd.website_date';

        $maxUnsignedBigint = '~0';
        $specialFromDate = $connection->getDatePartSql($specialFrom);
        $specialToDate = $connection->getDatePartSql($specialTo);
        $specialFromExpr = "{$specialFrom} IS NULL OR {$specialFromDate} <= {$currentDate}";
        $specialToExpr = "{$specialTo} IS NULL OR {$specialToDate} >= {$currentDate}";
        $specialPriceExpr = $connection->getCheckSql(
            "{$specialPrice} IS NOT NULL AND ({$specialFromExpr}) AND ({$specialToExpr})",
            $specialPrice,
            $maxUnsignedBigint
        );
        $tierPrice = $this->getTotalTierPriceExpression($price);
        $tierPriceExpr = $connection->getIfNullSql($tierPrice, $maxUnsignedBigint);
        $finalPrice = $connection->getLeastSql(
            [
            $price,
            $specialPriceExpr,
            $tierPriceExpr,
            ]
        );

        $select->columns(
            [
                'orig_price' => $connection->getIfNullSql($price, 0),
                'price' => $connection->getIfNullSql($finalPrice, 0),
                'min_price' => $connection->getIfNullSql($finalPrice, 0),
                'max_price' => $connection->getIfNullSql($finalPrice, 0),
                'tier_price' => $tierPrice,
                'base_tier' => $tierPrice,
            ]
        );

        if ($entityIds !== null) {
            $select->where('e.entity_id IN(?)', $entityIds);
        }

        /**
         * Add additional external limitation
         */
        $this->_eventManager->dispatch(
            'prepare_catalog_product_index_select',
            [
                'select' => $select,
                'entity_field' => new \Zend_Db_Expr('e.entity_id'),
                'website_field' => new \Zend_Db_Expr('cw.website_id'),
                'store_field' => new \Zend_Db_Expr('cs.store_id'),
            ]
        );

        return $select;
    }

    /**
     * Retrieve table name for custom option temporary aggregation data
     *
     * @return string
     */
    protected function _getCustomOptionAggregateTable()
    {
        return $this->tableStrategy->getTableName('catalog_product_index_price_opt_agr');
    }

    /**
     * Retrieve table name for custom option prices data
     *
     * @return string
     */
    protected function _getCustomOptionPriceTable()
    {
        return $this->tableStrategy->getTableName('catalog_product_index_price_opt');
    }

    /**
     * Prepare table structure for custom option temporary aggregation data
     *
     * @return $this
     */
    protected function _prepareCustomOptionAggregateTable()
    {
        $this->getConnection()->delete($this->_getCustomOptionAggregateTable());
        return $this;
    }

    /**
     * Prepare table structure for custom option prices data
     *
     * @return $this
     */
    protected function _prepareCustomOptionPriceTable()
    {
        $this->getConnection()->delete($this->_getCustomOptionPriceTable());
        return $this;
    }

    /**
     * Modify data in price index table.
     *
     * @param IndexTableStructure $finalPriceTable
     * @return void
     */
    private function modifyPriceIndex(IndexTableStructure $finalPriceTable) : void
    {
        foreach ($this->priceModifiers as $priceModifier) {
            $priceModifier->modifyPrice($finalPriceTable);
        }
    }

    /**
     * Apply custom option minimal and maximal price to temporary final price index table
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _applyCustomOption()
    {
        $connection = $this->getConnection();
        $finalPriceTable = $this->_getDefaultFinalPriceTable();
        $coaTable = $this->_getCustomOptionAggregateTable();
        $copTable = $this->_getCustomOptionPriceTable();
        $metadata = $this->getMetadataPool()->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class);

        $this->_prepareCustomOptionAggregateTable();
        $this->_prepareCustomOptionPriceTable();

        $select = $connection->select()->from(
            ['i' => $finalPriceTable],
            ['entity_id', 'customer_group_id', 'website_id']
        )->join(
            ['e' => $this->getTable('catalog_product_entity')],
            'e.entity_id = i.entity_id',
            []
        )->join(
            ['cw' => $this->getTable('store_website')],
            'cw.website_id = i.website_id',
            []
        )->join(
            ['csg' => $this->getTable('store_group')],
            'csg.group_id = cw.default_group_id',
            []
        )->join(
            ['cs' => $this->getTable('store')],
            'cs.store_id = csg.default_store_id',
            []
        )->join(
            ['o' => $this->getTable('catalog_product_option')],
            'o.product_id = e.' . $metadata->getLinkField(),
            ['option_id']
        )->join(
            ['ot' => $this->getTable('catalog_product_option_type_value')],
            'ot.option_id = o.option_id',
            []
        )->join(
            ['otpd' => $this->getTable('catalog_product_option_type_price')],
            'otpd.option_type_id = ot.option_type_id AND otpd.store_id = 0',
            []
        )->joinLeft(
            ['otps' => $this->getTable('catalog_product_option_type_price')],
            'otps.option_type_id = otpd.option_type_id AND otps.store_id = cs.store_id',
            []
        )->group(
            ['i.entity_id', 'i.customer_group_id', 'i.website_id', 'o.option_id']
        );

        $optPriceType = $connection->getCheckSql('otps.option_type_price_id > 0', 'otps.price_type', 'otpd.price_type');
        $optPriceValue = $connection->getCheckSql('otps.option_type_price_id > 0', 'otps.price', 'otpd.price');
        $minPriceRound = new \Zend_Db_Expr("ROUND(i.price * ({$optPriceValue} / 100), 4)");
        $minPriceExpr = $connection->getCheckSql("{$optPriceType} = 'fixed'", $optPriceValue, $minPriceRound);
        $minPriceMin = new \Zend_Db_Expr("MIN({$minPriceExpr})");
        $minPrice = $connection->getCheckSql("MIN(o.is_require) = 1", $minPriceMin, '0');

        $tierPriceRound = new \Zend_Db_Expr("ROUND(i.base_tier * ({$optPriceValue} / 100), 4)");
        $tierPriceExpr = $connection->getCheckSql("{$optPriceType} = 'fixed'", $optPriceValue, $tierPriceRound);
        $tierPriceMin = new \Zend_Db_Expr("MIN({$tierPriceExpr})");
        $tierPriceValue = $connection->getCheckSql("MIN(o.is_require) > 0", $tierPriceMin, 0);
        $tierPrice = $connection->getCheckSql("MIN(i.base_tier) IS NOT NULL", $tierPriceValue, "NULL");

        $maxPriceRound = new \Zend_Db_Expr("ROUND(i.price * ({$optPriceValue} / 100), 4)");
        $maxPriceExpr = $connection->getCheckSql("{$optPriceType} = 'fixed'", $optPriceValue, $maxPriceRound);
        $maxPrice = $connection->getCheckSql(
            "(MIN(o.type)='radio' OR MIN(o.type)='drop_down')",
            "MAX({$maxPriceExpr})",
            "SUM({$maxPriceExpr})"
        );

        $select->columns(
            [
                'min_price' => $minPrice,
                'max_price' => $maxPrice,
                'tier_price' => $tierPrice,
            ]
        );

        $query = $select->insertFromSelect($coaTable);
        $connection->query($query);

        $select = $connection->select()->from(
            ['i' => $finalPriceTable],
            ['entity_id', 'customer_group_id', 'website_id']
        )->join(
            ['e' => $this->getTable('catalog_product_entity')],
            'e.entity_id = i.entity_id',
            []
        )->join(
            ['cw' => $this->getTable('store_website')],
            'cw.website_id = i.website_id',
            []
        )->join(
            ['csg' => $this->getTable('store_group')],
            'csg.group_id = cw.default_group_id',
            []
        )->join(
            ['cs' => $this->getTable('store')],
            'cs.store_id = csg.default_store_id',
            []
        )->join(
            ['o' => $this->getTable('catalog_product_option')],
            'o.product_id = e.' . $metadata->getLinkField(),
            ['option_id']
        )->join(
            ['opd' => $this->getTable('catalog_product_option_price')],
            'opd.option_id = o.option_id AND opd.store_id = 0',
            []
        )->joinLeft(
            ['ops' => $this->getTable('catalog_product_option_price')],
            'ops.option_id = opd.option_id AND ops.store_id = cs.store_id',
            []
        );

        $optPriceType = $connection->getCheckSql('ops.option_price_id > 0', 'ops.price_type', 'opd.price_type');
        $optPriceValue = $connection->getCheckSql('ops.option_price_id > 0', 'ops.price', 'opd.price');

        $minPriceRound = new \Zend_Db_Expr("ROUND(i.price * ({$optPriceValue} / 100), 4)");
        $priceExpr = $connection->getCheckSql("{$optPriceType} = 'fixed'", $optPriceValue, $minPriceRound);
        $minPrice = $connection->getCheckSql("{$priceExpr} > 0 AND o.is_require = 1", $priceExpr, 0);

        $maxPrice = $priceExpr;

        $tierPriceRound = new \Zend_Db_Expr("ROUND(i.base_tier * ({$optPriceValue} / 100), 4)");
        $tierPriceExpr = $connection->getCheckSql("{$optPriceType} = 'fixed'", $optPriceValue, $tierPriceRound);
        $tierPriceValue = $connection->getCheckSql("{$tierPriceExpr} > 0 AND o.is_require = 1", $tierPriceExpr, 0);
        $tierPrice = $connection->getCheckSql("i.base_tier IS NOT NULL", $tierPriceValue, "NULL");

        $select->columns(
            [
                'min_price' => $minPrice,
                'max_price' => $maxPrice,
                'tier_price' => $tierPrice,
            ]
        );

        $query = $select->insertFromSelect($coaTable);
        $connection->query($query);

        $select = $connection->select()->from(
            [$coaTable],
            [
                'entity_id',
                'customer_group_id',
                'website_id',
                'min_price' => 'SUM(min_price)',
                'max_price' => 'SUM(max_price)',
                'tier_price' => 'SUM(tier_price)',
            ]
        )->group(
            ['entity_id', 'customer_group_id', 'website_id']
        );
        $query = $select->insertFromSelect($copTable);
        $connection->query($query);

        $table = ['i' => $finalPriceTable];
        $select = $connection->select()->join(
            ['io' => $copTable],
            'i.entity_id = io.entity_id AND i.customer_group_id = io.customer_group_id' .
            ' AND i.website_id = io.website_id',
            []
        );
        $select->columns(
            [
                'min_price' => new \Zend_Db_Expr('i.min_price + io.min_price'),
                'max_price' => new \Zend_Db_Expr('i.max_price + io.max_price'),
                'tier_price' => $connection->getCheckSql(
                    'i.tier_price IS NOT NULL',
                    'i.tier_price + io.tier_price',
                    'NULL'
                ),
            ]
        );
        $query = $select->crossUpdateFromSelect($table);
        $connection->query($query);

        $connection->delete($coaTable);
        $connection->delete($copTable);

        return $this;
    }

    /**
     * Mode Final Prices index to primary temporary index table
     *
     * @param int[]|null $entityIds
     * @return $this
     */
    protected function _movePriceDataToIndexTable($entityIds = null)
    {
        $columns = [
            'entity_id' => 'entity_id',
            'customer_group_id' => 'customer_group_id',
            'website_id' => 'website_id',
            'tax_class_id' => 'tax_class_id',
            'price' => 'orig_price',
            'final_price' => 'price',
            'min_price' => 'min_price',
            'max_price' => 'max_price',
            'tier_price' => 'tier_price',
        ];

        $connection = $this->getConnection();
        $table = $this->_getDefaultFinalPriceTable();
        $select = $connection->select()->from($table, $columns);

        if ($entityIds !== null) {
            $select->where('entity_id in (?)', count($entityIds) > 0 ? $entityIds : 0);
        }

        $query = $select->insertFromSelect($this->getIdxTable(), [], false);
        $connection->query($query);

        $connection->delete($table);

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
     * Retrieve temporary index table name
     *
     * @param string $table
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getIdxTable($table = null)
    {
        return $this->tableStrategy->getTableName('catalog_product_index_price');
    }

    /**
     * Check if product exists.
     *
     * @return bool
     */
    protected function hasEntity()
    {
        if ($this->hasEntity === null) {
            $reader = $this->getConnection();

            $select = $reader->select()->from(
                [$this->getTable('catalog_product_entity')],
                ['count(entity_id)']
            )->where(
                'type_id=?',
                $this->getTypeId()
            );
            $this->hasEntity = (int)$reader->fetchOne($select) > 0;
        }

        return $this->hasEntity;
    }

    /**
     * Get total tier price expression.
     *
     * @param \Zend_Db_Expr $priceExpression
     * @return \Zend_Db_Expr
     */
    private function getTotalTierPriceExpression(\Zend_Db_Expr $priceExpression)
    {
        $maxUnsignedBigint = '~0';

        return $this->getConnection()->getCheckSql(
            implode(
                ' AND ',
                [
                    'tier_price_1.value_id is NULL',
                    'tier_price_2.value_id is NULL',
                    'tier_price_3.value_id is NULL',
                    'tier_price_4.value_id is NULL'
                ]
            ),
            'NULL',
            $this->getConnection()->getLeastSql(
                [
                $this->getConnection()->getIfNullSql(
                    $this->getTierPriceExpressionForTable('tier_price_1', $priceExpression),
                    $maxUnsignedBigint
                ),
                $this->getConnection()->getIfNullSql(
                    $this->getTierPriceExpressionForTable('tier_price_2', $priceExpression),
                    $maxUnsignedBigint
                ),
                $this->getConnection()->getIfNullSql(
                    $this->getTierPriceExpressionForTable('tier_price_3', $priceExpression),
                    $maxUnsignedBigint
                ),
                $this->getConnection()->getIfNullSql(
                    $this->getTierPriceExpressionForTable('tier_price_4', $priceExpression),
                    $maxUnsignedBigint
                ),
                ]
            )
        );
    }

    /**
     * Get tier price expression for table.
     *
     * @param string $tableAlias
     * @param \Zend_Db_Expr $priceExpression
     * @return \Zend_Db_Expr
     */
    private function getTierPriceExpressionForTable($tableAlias, \Zend_Db_Expr $priceExpression)
    {
        return $this->getConnection()->getCheckSql(
            sprintf('%s.value = 0', $tableAlias),
            sprintf(
                'ROUND(%s * (1 - ROUND(%s.percentage_value * cwd.rate, 4) / 100), 4)',
                $priceExpression,
                $tableAlias
            ),
            sprintf('ROUND(%s.value * cwd.rate, 4)', $tableAlias)
        );
    }
}
