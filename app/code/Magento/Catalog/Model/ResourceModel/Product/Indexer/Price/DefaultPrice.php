<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel\Product\Indexer\Price;

use Magento\Catalog\Model\Indexer\Product\Price\DimensionProviderFactory;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\AbstractIndexer;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\Query\BaseFinalPrice;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\Query\CustomOptionsPrice;
use Magento\Customer\Model\Indexer\MultiDimensional\CustomerGroupDataProvider;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\Indexer\MultiDimensional\WebsiteDataProvider;
use Magento\Catalog\Model\Indexer\Product\Price\ModeSwitcher;

/**
 * Default Product Type Price Indexer Resource model
 * For correctly work need define product type id
 *
 * @api
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
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
     * @var BaseFinalPrice
     */
    private $baseFinalPrice;

    /**
     * @var CustomOptionsPrice
     */
    private $customOptionsPrice;

    /**
     * @var DimensionCollectionFactory
     */
    private $dimensionCollectionFactory;

    /**
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
        BaseFinalPrice $baseFinalPrice = null,
        CustomOptionsPrice $customOptionsPrice = null,
        DimensionProviderFactory $dimensionCollectionFactory = null,
        array $priceModifiers = []
    ) {
        $this->_eventManager = $eventManager;
        $this->moduleManager = $moduleManager;
        parent::__construct($context, $tableStrategy, $eavConfig, $connectionName);

        $this->indexTableStructureFactory = $indexTableStructureFactory ?:
            ObjectManager::getInstance()->get(IndexTableStructureFactory::class);
        foreach ($priceModifiers as $priceModifier) {
            if (!($priceModifier instanceof PriceModifierInterface)) {
                throw new \InvalidArgumentException(
                    'Argument \'priceModifiers\' must be of the type ' . PriceModifierInterface::class . '[]'
                );
            }

            $this->priceModifiers[] = $priceModifier;
        }
        $this->baseFinalPrice = $baseFinalPrice ?? ObjectManager::getInstance()->get(BaseFinalPrice::class);
        $this->customOptionsPrice = $baseFinalPrice ?? ObjectManager::getInstance()->get(CustomOptionsPrice::class);
        $this->dimensionCollectionFactory = $dimensionCollectionFactory
            ?? ObjectManager::getInstance()->get(DimensionProviderFactory::class);
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
     * @deprecated
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

        $finalPriceTable = $this->indexTableStructureFactory->create([
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
        ]);

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

        $dimensions = $this->dimensionCollectionFactory->createByMode(
            ModeSwitcher::INPUT_KEY_WEBSITE_AND_CUSTOMER_GROUP
        );
        foreach ($dimensions as $dimension) {
            $select = $this->getSelect(
                $entityIds,
                $type,
                $dimension[WebsiteDataProvider::DIMENSION_NAME]->getValue(),
                $dimension[CustomerGroupDataProvider::DIMENSION_NAME]->getValue()
            );
            $query = $select->insertFromSelect($finalPriceTable->getTableName(), [], false);
            $this->getConnection()->query($query);
        }
        $this->applyDiscountPrices($finalPriceTable);

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
    protected function getSelect($entityIds = null, $type = null, int $websiteId = null, int $customerGroupId = null)
    {
        $select = $this->baseFinalPrice->getQuery($websiteId, $customerGroupId, $type, $entityIds);
        /**
         * Add additional external limitation
         */
        $this->_eventManager->dispatch(
            'prepare_catalog_product_index_select',
            [
                'select' => $select,
                'entity_field' => new \Zend_Db_Expr('e.entity_id'),
                'website_field' => new \Zend_Db_Expr('pw.website_id'),
                'store_field' => new \Zend_Db_Expr('cwd.default_store_id'),
                'website_id' => $websiteId,
                'customer_group_id' => $customerGroupId,
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
     * Apply discount prices to final price index table.
     *
     * @param IndexTableStructure $finalPriceTable
     * @return void
     */
    private function applyDiscountPrices(IndexTableStructure $finalPriceTable)
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
        // no need to run all queries if current products have no custom options
        if (!$this->checkIfCustomOptionsExist()) {
            return $this;
        }

        $connection = $this->getConnection();
        $finalPriceTable = $this->_getDefaultFinalPriceTable();

        $coaTable = $this->_getCustomOptionAggregateTable();
        $this->_prepareCustomOptionAggregateTable();

        $copTable = $this->_getCustomOptionPriceTable();
        $this->_prepareCustomOptionPriceTable();

        // prepare prices for products with custom options that has multiple values
        $select = $this->customOptionsPrice->getSelectForOptionsWithMultipleValues($finalPriceTable);
        $query = $select->insertFromSelect($coaTable);
        $connection->query($query);

        // prepare prices for products with custom options that has single value
        $select = $this->customOptionsPrice->getSelectForOptionsWithOneValue($finalPriceTable);
        $query = $select->insertFromSelect($coaTable);
        $connection->query($query);

        // aggregate prices from previous two cases into one table
        $select = $this->customOptionsPrice->getSelectAggregated($coaTable);
        $query = $select->insertFromSelect($copTable);
        $connection->query($query);

        // update tmp price index with prices from custom options (from previous aggregated table)
        $select = $this->customOptionsPrice->getSelectForUpdate($copTable);
        $query = $select->crossUpdateFromSelect(['i' => $finalPriceTable]);
        $connection->query($query);

        $connection->delete($coaTable);
        $connection->delete($copTable);

        return $this;
    }

    private function checkIfCustomOptionsExist()
    {
        $select = $this->getConnection()
            ->select()
            ->from(
                ['i' => $this->_getDefaultFinalPriceTable()],
                ['entity_id']
            )->join(
                ['o' => $this->getTable('catalog_product_option')],
                'o.product_id = i.entity_id',
                []
            );

        return !empty($this->getConnection()->fetchRow($select));
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
}
