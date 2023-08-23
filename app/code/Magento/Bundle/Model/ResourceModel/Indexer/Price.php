<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Model\ResourceModel\Indexer;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\BasePriceModifier;
use Magento\Framework\DB\Select;
use Magento\Framework\Indexer\DimensionalIndexerInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Catalog\Model\Indexer\Product\Price\TableMaintainer;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\IndexTableStructureFactory;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\IndexTableStructure;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\Query\JoinAttributeProcessor;
use Magento\Customer\Model\Indexer\CustomerGroupDimensionProvider;
use Magento\Store\Model\Indexer\WebsiteDimensionProvider;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\CatalogInventory\Model\Stock;

/**
 * Bundle products Price indexer resource model
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Price implements DimensionalIndexerInterface
{
    /**
     * @var IndexTableStructureFactory
     */
    private $indexTableStructureFactory;

    /**
     * @var TableMaintainer
     */
    private $tableMaintainer;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @var bool
     */
    private $fullReindexAction;

    /**
     * @var string
     */
    private $connectionName;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;

    /**
     * Mapping between dimensions and field in database
     *
     * @var array
     */
    private $dimensionToFieldMapper = [
        WebsiteDimensionProvider::DIMENSION_NAME => 'pw.website_id',
        CustomerGroupDimensionProvider::DIMENSION_NAME => 'cg.customer_group_id',
    ];

    /**
     * @var BasePriceModifier
     */
    private $basePriceModifier;

    /**
     * @var JoinAttributeProcessor
     */
    private $joinAttributeProcessor;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    /**
     * @var string
     */
    private $tmpBundlePriceTable;

    /**
     * @var string
     */
    private $tmpBundleSelectionTable;

    /**
     * @var string
     */
    private $tmpBundleOptionTable;

    /**
     * @param IndexTableStructureFactory $indexTableStructureFactory
     * @param TableMaintainer $tableMaintainer
     * @param MetadataPool $metadataPool
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param BasePriceModifier $basePriceModifier
     * @param JoinAttributeProcessor $joinAttributeProcessor
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param bool $fullReindexAction
     * @param string $connectionName
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        IndexTableStructureFactory $indexTableStructureFactory,
        TableMaintainer $tableMaintainer,
        MetadataPool $metadataPool,
        \Magento\Framework\App\ResourceConnection $resource,
        BasePriceModifier $basePriceModifier,
        JoinAttributeProcessor $joinAttributeProcessor,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Module\Manager $moduleManager,
        $fullReindexAction = false,
        $connectionName = 'indexer'
    ) {
        $this->indexTableStructureFactory = $indexTableStructureFactory;
        $this->tableMaintainer = $tableMaintainer;
        $this->connectionName = $connectionName;
        $this->metadataPool = $metadataPool;
        $this->resource = $resource;
        $this->fullReindexAction = $fullReindexAction;
        $this->basePriceModifier = $basePriceModifier;
        $this->joinAttributeProcessor = $joinAttributeProcessor;
        $this->eventManager = $eventManager;
        $this->moduleManager = $moduleManager;
    }

    /**
     * @inheritdoc
     * @param array $dimensions
     * @param \Traversable $entityIds
     * @throws \Exception
     */
    public function executeByDimensions(array $dimensions, \Traversable $entityIds)
    {
        $this->tableMaintainer->createMainTmpTable($dimensions);

        $temporaryPriceTable = $this->indexTableStructureFactory->create(
            [
                'tableName' => $this->tableMaintainer->getMainTmpTable($dimensions),
                'entityField' => 'entity_id',
                'customerGroupField' => 'customer_group_id',
                'websiteField' => 'website_id',
                'taxClassField' => 'tax_class_id',
                'originalPriceField' => 'price',
                'finalPriceField' => 'final_price',
                'minPriceField' => 'min_price',
                'maxPriceField' => 'max_price',
                'tierPriceField' => 'tier_price',
            ]
        );

        $entityIds = iterator_to_array($entityIds);

        $this->prepareTierPriceIndex($dimensions, $entityIds);

        $this->prepareBundlePriceTable();

        $this->prepareBundlePriceByType(
            \Magento\Bundle\Model\Product\Price::PRICE_TYPE_FIXED,
            $dimensions,
            $entityIds
        );

        $this->prepareBundlePriceByType(
            \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC,
            $dimensions,
            $entityIds
        );

        $this->calculateBundleOptionPrice($temporaryPriceTable, $dimensions);

        $this->basePriceModifier->modifyPrice($temporaryPriceTable, $entityIds);
    }

    /**
     * Retrieve temporary price index table name for fixed bundle products
     *
     * @return string
     */
    private function getBundlePriceTable()
    {
        if ($this->tmpBundlePriceTable === null) {
            $this->tmpBundlePriceTable = $this->getTable('catalog_product_index_price_bundle_temp');
            $this->getConnection()->createTemporaryTableLike(
                $this->tmpBundlePriceTable,
                $this->getTable('catalog_product_index_price_bundle_tmp'),
                true
            );
        }

        return $this->tmpBundlePriceTable;
    }

    /**
     * Retrieve table name for temporary bundle selection prices index
     *
     * @return string
     */
    private function getBundleSelectionTable()
    {
        if ($this->tmpBundleSelectionTable === null) {
            $this->tmpBundleSelectionTable = $this->getTable('catalog_product_index_price_bundle_sel_temp');
            $this->getConnection()->createTemporaryTableLike(
                $this->tmpBundleSelectionTable,
                $this->getTable('catalog_product_index_price_bundle_sel_tmp'),
                true
            );
        }

        return $this->tmpBundleSelectionTable;
    }

    /**
     * Retrieve table name for temporary bundle option prices index
     *
     * @return string
     */
    private function getBundleOptionTable()
    {
        if ($this->tmpBundleOptionTable === null) {
            $this->tmpBundleOptionTable = $this->getTable('catalog_product_index_price_bundle_opt_temp');
            $this->getConnection()->createTemporaryTableLike(
                $this->tmpBundleOptionTable,
                $this->getTable('catalog_product_index_price_bundle_opt_tmp'),
                true
            );
        }

        return $this->tmpBundleOptionTable;
    }

    /**
     * Prepare temporary price index table for fixed bundle products
     *
     * @return $this
     */
    private function prepareBundlePriceTable()
    {
        $this->getConnection()->delete($this->getBundlePriceTable());
        return $this;
    }

    /**
     * Prepare table structure for temporary bundle selection prices index
     *
     * @return $this
     */
    private function prepareBundleSelectionTable()
    {
        $this->getConnection()->delete($this->getBundleSelectionTable());
        return $this;
    }

    /**
     * Prepare table structure for temporary bundle option prices index
     *
     * @return $this
     */
    private function prepareBundleOptionTable()
    {
        $this->getConnection()->delete($this->getBundleOptionTable());
        return $this;
    }

    /**
     * Prepare temporary price index data for bundle products by price type
     *
     * @param int $priceType
     * @param array $dimensions
     * @param int|array $entityIds the entity ids limitation
     * @return void
     * @throws \Exception
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    private function prepareBundlePriceByType($priceType, array $dimensions, $entityIds = null)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            ['e' => $this->getTable('catalog_product_entity')],
            ['entity_id']
        )->joinInner(
            ['cg' => $this->getTable('customer_group')],
            array_key_exists(CustomerGroupDimensionProvider::DIMENSION_NAME, $dimensions)
                ? sprintf(
                    '%s = %s',
                    $this->dimensionToFieldMapper[CustomerGroupDimensionProvider::DIMENSION_NAME],
                    $dimensions[CustomerGroupDimensionProvider::DIMENSION_NAME]->getValue()
                ) : '',
            ['customer_group_id']
        )->joinInner(
            ['pw' => $this->getTable('catalog_product_website')],
            'pw.product_id = e.entity_id',
            ['pw.website_id']
        )->joinInner(
            ['cwd' => $this->getTable('catalog_product_index_website')],
            'pw.website_id = cwd.website_id',
            []
        )->joinLeft(
            ['cgw' => $this->getTable('customer_group_excluded_website')],
            'cg.customer_group_id = cgw.customer_group_id AND pw.website_id = cgw.website_id',
            []
        );
        $select->joinLeft(
            ['tp' => $this->getTable('catalog_product_index_tier_price')],
            'tp.entity_id = e.entity_id AND tp.website_id = pw.website_id' .
            ' AND tp.customer_group_id = cg.customer_group_id',
            []
        )->where(
            'e.type_id=?',
            \Magento\Bundle\Ui\DataProvider\Product\Listing\Collector\BundlePrice::PRODUCT_TYPE
        );

        foreach ($dimensions as $dimension) {
            if (!isset($this->dimensionToFieldMapper[$dimension->getName()])) {
                throw new \LogicException(
                    'Provided dimension is not valid for Price indexer: ' . $dimension->getName()
                );
            }
            $select->where($this->dimensionToFieldMapper[$dimension->getName()] . ' = ?', $dimension->getValue());
        }

        $this->joinAttributeProcessor->process($select, 'status', Status::STATUS_ENABLED);
        if ($this->moduleManager->isEnabled('Magento_Tax')) {
            $taxClassId = $this->joinAttributeProcessor->process($select, 'tax_class_id');
        } else {
            $taxClassId = new \Zend_Db_Expr('0');
        }

        if ($priceType == \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC) {
            $select->columns(['tax_class_id' => new \Zend_Db_Expr('0')]);
        } else {
            $select->columns(
                ['tax_class_id' => $connection->getCheckSql($taxClassId . ' IS NOT NULL', $taxClassId, 0)]
            );
        }

        $this->joinAttributeProcessor->process($select, 'price_type', $priceType);

        $price = $this->joinAttributeProcessor->process($select, 'price');
        $specialPrice = $this->joinAttributeProcessor->process($select, 'special_price');
        $specialFrom = $this->joinAttributeProcessor->process($select, 'special_from_date');
        $specialTo = $this->joinAttributeProcessor->process($select, 'special_to_date');
        $currentDate = new \Zend_Db_Expr('cwd.website_date');

        $specialFromDate = $connection->getDatePartSql($specialFrom);
        $specialToDate = $connection->getDatePartSql($specialTo);
        $specialFromExpr = "{$specialFrom} IS NULL OR {$specialFromDate} <= {$currentDate}";
        $specialToExpr = "{$specialTo} IS NULL OR {$specialToDate} >= {$currentDate}";
        $specialExpr = "{$specialPrice} IS NOT NULL AND {$specialPrice} > 0 AND {$specialPrice} < 100"
            . " AND {$specialFromExpr} AND {$specialToExpr}";
        $tierExpr = new \Zend_Db_Expr('tp.min_price');

        if ($priceType == \Magento\Bundle\Model\Product\Price::PRICE_TYPE_FIXED) {
            $specialPriceExpr = $connection->getCheckSql(
                $specialExpr,
                'ROUND(' . $price . ' * (' . $specialPrice . '  / 100), 4)',
                'NULL'
            );
            $tierPrice = $connection->getCheckSql(
                $tierExpr . ' IS NOT NULL',
                'ROUND((1 - ' . $tierExpr . ' / 100) * ' . $price . ', 4)',
                'NULL'
            );
            $finalPrice = $connection->getLeastSql(
                [
                    $price,
                    $connection->getIfNullSql($specialPriceExpr, $price),
                    $connection->getIfNullSql($tierPrice, $price),
                ]
            );
        } else {
            $finalPrice = new \Zend_Db_Expr('0');
            $tierPrice = $connection->getCheckSql($tierExpr . ' IS NOT NULL', '0', 'NULL');
        }

        $select->columns(
            [
                'price_type' => new \Zend_Db_Expr($priceType),
                'special_price' => $connection->getCheckSql($specialExpr, $specialPrice, '0'),
                'tier_percent' => $tierExpr,
                'orig_price' => $connection->getIfNullSql($price, '0'),
                'price' => $finalPrice,
                'min_price' => $finalPrice,
                'max_price' => $finalPrice,
                'tier_price' => $tierPrice,
                'base_tier' => $tierPrice,
            ]
        );

        if ($entityIds !== null) {
            $select->where('e.entity_id IN(?)', $entityIds);
        }

        // exclude websites that are limited for customer group
        $select->where('cgw.website_id IS NULL');

        /**
         * Add additional external limitation
         */
        $this->eventManager->dispatch(
            'catalog_product_prepare_index_select',
            [
                'select' => $select,
                'entity_field' => new \Zend_Db_Expr('e.entity_id'),
                'website_field' => new \Zend_Db_Expr('pw.website_id'),
                'store_field' => new \Zend_Db_Expr('cwd.default_store_id')
            ]
        );

        $this->tableMaintainer->insertFromSelect($select, $this->getBundlePriceTable(), []);
    }

    /**
     * Calculate fixed bundle product selections price
     *
     * @param IndexTableStructure $priceTable
     * @param array $dimensions
     *
     * @return void
     * @throws \Exception
     */
    private function calculateBundleOptionPrice($priceTable, $dimensions)
    {
        $connection = $this->getConnection();

        $this->prepareBundleSelectionTable();
        $this->calculateFixedBundleSelectionPrice();
        $this->calculateDynamicBundleSelectionPrice($dimensions);

        $this->prepareBundleOptionTable();

        $select = $connection->select()->from(
            $this->getBundleSelectionTable(),
            ['entity_id', 'customer_group_id', 'website_id', 'option_id']
        )->group(
            ['entity_id', 'customer_group_id', 'website_id', 'option_id']
        );
        $minPrice = $connection->getCheckSql('is_required = 1', 'price', 'NULL');
        $tierPrice = $connection->getCheckSql('is_required = 1', 'tier_price', 'NULL');
        $select->columns(
            [
                'min_price' => new \Zend_Db_Expr('MIN(' . $minPrice . ')'),
                'alt_price' => new \Zend_Db_Expr('MIN(price)'),
                'max_price' => $connection->getCheckSql('group_type = 0', 'MAX(price)', 'SUM(price)'),
                'tier_price' => new \Zend_Db_Expr('MIN(' . $tierPrice . ')'),
                'alt_tier_price' => new \Zend_Db_Expr('MIN(tier_price)'),
            ]
        );

        $this->tableMaintainer->insertFromSelect($select, $this->getBundleOptionTable(), []);

        $this->getConnection()->delete($priceTable->getTableName());
        $this->applyBundlePrice($priceTable);
        $this->applyBundleOptionPrice($priceTable);
    }

    /**
     * Get base select for bundle selection price
     *
     * @return Select
     * @throws \Exception
     */
    private function getBaseBundleSelectionPriceSelect(): Select
    {
        $metadata = $this->metadataPool->getMetadata(ProductInterface::class);
        $linkField = $metadata->getLinkField();

        $select = $this->getConnection()->select()->from(
            ['i' => $this->getBundlePriceTable()],
            ['entity_id', 'customer_group_id', 'website_id']
        )->join(
            ['parent_product' => $this->getTable('catalog_product_entity')],
            'parent_product.entity_id = i.entity_id',
            []
        )->join(
            ['bo' => $this->getTable('catalog_product_bundle_option')],
            "bo.parent_id = parent_product.$linkField",
            ['option_id']
        )->join(
            ['bs' => $this->getTable('catalog_product_bundle_selection')],
            'bs.option_id = bo.option_id',
            ['selection_id']
        );

        return $select;
    }

    /**
     * Get base select for bundle selection price update
     *
     * @return Select
     * @throws \Exception
     */
    private function getBaseBundleSelectionPriceUpdateSelect(): Select
    {
        $metadata = $this->metadataPool->getMetadata(ProductInterface::class);
        $linkField = $metadata->getLinkField();
        $bundleSelectionTable = $this->getBundleSelectionTable();

        $select = $this->getConnection()->select()
        ->join(
            ['i' => $this->getBundlePriceTable()],
            "i.entity_id = $bundleSelectionTable.entity_id
             AND i.customer_group_id = $bundleSelectionTable.customer_group_id
             AND i.website_id = $bundleSelectionTable.website_id",
            []
        )->join(
            ['parent_product' => $this->getTable('catalog_product_entity')],
            'parent_product.entity_id = i.entity_id',
            []
        )->join(
            ['bo' => $this->getTable('catalog_product_bundle_option')],
            "bo.parent_id = parent_product.$linkField AND bo.option_id = $bundleSelectionTable.option_id",
            ['option_id']
        )->join(
            ['bs' => $this->getTable('catalog_product_bundle_selection')],
            "bs.option_id = bo.option_id AND bs.selection_id = $bundleSelectionTable.selection_id",
            ['selection_id']
        );

        return $select;
    }

    /**
     * Apply selections price for fixed bundles
     *
     * @return void
     * @throws \Exception
     */
    private function applyFixedBundleSelectionPrice()
    {
        $connection = $this->getConnection();

        $selectionPriceValue = 'bsp.selection_price_value';
        $selectionPriceType = 'bsp.selection_price_type';
        $priceExpr = new \Zend_Db_Expr(
            $connection->getCheckSql(
                $selectionPriceType . ' = 1',
                'ROUND(i.price * (' . $selectionPriceValue . ' / 100),4)',
                $connection->getCheckSql(
                    'i.special_price > 0 AND i.special_price < 100',
                    'ROUND(' . $selectionPriceValue . ' * (i.special_price / 100),4)',
                    $selectionPriceValue
                )
            ) . '* bs.selection_qty'
        );
        $tierExpr = $connection->getCheckSql(
            'i.base_tier IS NOT NULL',
            $connection->getCheckSql(
                $selectionPriceType . ' = 1',
                'ROUND(i.base_tier - (i.base_tier * (' . $selectionPriceValue . ' / 100)),4)',
                $connection->getCheckSql(
                    'i.tier_percent > 0',
                    'ROUND((1 - i.tier_percent / 100) * ' . $selectionPriceValue . ',4)',
                    $selectionPriceValue
                )
            ) . ' * bs.selection_qty',
            'NULL'
        );
        $priceExpr = $connection->getLeastSql(
            [
                $priceExpr,
                $connection->getIfNullSql($tierExpr, $priceExpr),
            ]
        );

        $select = $this->getBaseBundleSelectionPriceUpdateSelect();
        $select->joinInner(
            ['bsp' => $this->getTable('catalog_product_bundle_selection_price')],
            'bs.selection_id = bsp.selection_id AND bsp.website_id = i.website_id',
            []
        )->where(
            'i.price_type=?',
            \Magento\Bundle\Model\Product\Price::PRICE_TYPE_FIXED
        )->columns(
            [
                'group_type' => $connection->getCheckSql("bo.type = 'select' OR bo.type = 'radio'", '0', '1'),
                'is_required' => 'bo.required',
                'price' => $priceExpr,
                'tier_price' => $tierExpr,
            ]
        );
        $query = $select->crossUpdateFromSelect($this->getBundleSelectionTable());
        $connection->query($query);
    }

    /**
     * Calculate selections price for fixed bundles
     *
     * @return void
     * @throws \Exception
     */
    private function calculateFixedBundleSelectionPrice()
    {
        $connection = $this->getConnection();

        $selectionPriceValue = 'bs.selection_price_value';
        $selectionPriceType = 'bs.selection_price_type';
        $priceExpr = new \Zend_Db_Expr(
            $connection->getCheckSql(
                $selectionPriceType . ' = 1',
                'ROUND(i.price * (' . $selectionPriceValue . ' / 100),4)',
                $connection->getCheckSql(
                    'i.special_price > 0 AND i.special_price < 100',
                    'ROUND(' . $selectionPriceValue . ' * (i.special_price / 100),4)',
                    $selectionPriceValue
                )
            ) . '* bs.selection_qty'
        );
        $tierExpr = $connection->getCheckSql(
            'i.base_tier IS NOT NULL',
            $connection->getCheckSql(
                $selectionPriceType . ' = 1',
                'ROUND(i.base_tier - (i.base_tier * (' . $selectionPriceValue . ' / 100)),4)',
                $connection->getCheckSql(
                    'i.tier_percent > 0',
                    'ROUND((1 - i.tier_percent / 100) * ' . $selectionPriceValue . ',4)',
                    $selectionPriceValue
                )
            ) . ' * bs.selection_qty',
            'NULL'
        );
        $priceExpr = $connection->getLeastSql(
            [
                $priceExpr,
                $connection->getIfNullSql($tierExpr, $priceExpr),
            ]
        );

        $select = $this->getBaseBundleSelectionPriceSelect();
        $select->where(
            'i.price_type=?',
            \Magento\Bundle\Model\Product\Price::PRICE_TYPE_FIXED
        )->columns(
            [
                'group_type' => $connection->getCheckSql("bo.type = 'select' OR bo.type = 'radio'", '0', '1'),
                'is_required' => 'bo.required',
                'price' => $priceExpr,
                'tier_price' => $tierExpr,
            ]
        );
        $this->tableMaintainer->insertFromSelect($select, $this->getBundleSelectionTable(), []);

        $this->applyFixedBundleSelectionPrice();
    }

    /**
     * Calculate selections price for dynamic bundles
     *
     * @param array $dimensions
     * @return void
     * @throws \Exception
     */
    private function calculateDynamicBundleSelectionPrice($dimensions)
    {
        $connection = $this->getConnection();

        $price = 'idx.min_price * bs.selection_qty';
        $specialExpr = $connection->getCheckSql(
            'i.special_price > 0 AND i.special_price < 100',
            'ROUND(' . $price . ' * (i.special_price / 100), 4)',
            $price
        );
        $tierExpr = $connection->getCheckSql(
            'i.tier_percent IS NOT NULL',
            'ROUND((1 - i.tier_percent / 100) * ' . $price . ', 4)',
            'NULL'
        );
        $priceExpr = $connection->getLeastSql(
            [
                $specialExpr,
                $connection->getIfNullSql($tierExpr, $price),
            ]
        );

        $select = $this->getBaseBundleSelectionPriceSelect();
        $select->join(
            ['idx' => $this->getMainTable($dimensions)],
            'bs.product_id = idx.entity_id AND i.customer_group_id = idx.customer_group_id' .
            ' AND i.website_id = idx.website_id',
            []
        )->where(
            'i.price_type=?',
            \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC
        )->columns(
            [
                'group_type' => $connection->getCheckSql("bo.type = 'select' OR bo.type = 'radio'", '0', '1'),
                'is_required' => 'bo.required',
                'price' => $priceExpr,
                'tier_price' => $tierExpr,
            ]
        );
        $select->join(
            ['si' => $this->getTable('cataloginventory_stock_status')],
            'si.product_id = bs.product_id',
            []
        );
        $select->where('si.stock_status = ?', Stock::STOCK_IN_STOCK);

        $this->tableMaintainer->insertFromSelect($select, $this->getBundleSelectionTable(), []);
    }

    /**
     * Prepare percentage tier price for bundle products
     *
     * @param array $dimensions
     * @param array $entityIds
     * @return void
     * @throws \Exception
     */
    private function prepareTierPriceIndex($dimensions, $entityIds)
    {
        $connection = $this->getConnection();
        $metadata = $this->metadataPool->getMetadata(ProductInterface::class);
        $linkField = $metadata->getLinkField();
        // remove index by bundle products
        $select = $connection->select()->from(
            ['i' => $this->getTable('catalog_product_index_tier_price')],
            null
        )->join(
            ['e' => $this->getTable('catalog_product_entity')],
            "i.entity_id=e.entity_id",
            []
        )->where(
            'e.type_id=?',
            \Magento\Bundle\Ui\DataProvider\Product\Listing\Collector\BundlePrice::PRODUCT_TYPE
        );
        $query = $select->deleteFromSelect('i');
        $connection->query($query);

        $select = $connection->select()->from(
            ['tp' => $this->getTable('catalog_product_entity_tier_price')],
            ['e.entity_id']
        )->join(
            ['e' => $this->getTable('catalog_product_entity')],
            "tp.{$linkField} = e.{$linkField}",
            []
        )->join(
            ['cg' => $this->getTable('customer_group')],
            'tp.all_groups = 1 OR (tp.all_groups = 0 AND tp.customer_group_id = cg.customer_group_id)',
            ['customer_group_id']
        )->join(
            ['pw' => $this->getTable('store_website')],
            'tp.website_id = 0 OR tp.website_id = pw.website_id',
            ['website_id']
        )->joinLeft(
            // customer group website limitations
            ['cgw' => $this->getTable('customer_group_excluded_website')],
            'cg.customer_group_id = cgw.customer_group_id AND pw.website_id = cgw.website_id',
            []
        )->where(
            'pw.website_id != 0'
        )->where(
            'e.type_id=?',
            \Magento\Bundle\Ui\DataProvider\Product\Listing\Collector\BundlePrice::PRODUCT_TYPE
        )->columns(
            new \Zend_Db_Expr('MIN(tp.value)')
        )->group(
            ['e.entity_id', 'cg.customer_group_id', 'pw.website_id']
        );

        if (!empty($entityIds)) {
            $select->where('e.entity_id IN(?)', $entityIds);
        }

        // exclude websites that are limited for customer group
        $select->where('cgw.website_id IS NULL');

        foreach ($dimensions as $dimension) {
            if (!isset($this->dimensionToFieldMapper[$dimension->getName()])) {
                throw new \LogicException(
                    'Provided dimension is not valid for Price indexer: ' . $dimension->getName()
                );
            }
            $select->where($this->dimensionToFieldMapper[$dimension->getName()] . ' = ?', $dimension->getValue());
        }

        $this->tableMaintainer->insertFromSelect($select, $this->getTable('catalog_product_index_tier_price'), []);
    }

    /**
     * Create bundle price.
     *
     * @param IndexTableStructure $priceTable
     * @return void
     */
    private function applyBundlePrice($priceTable): void
    {
        $select = $this->getConnection()->select();
        $select->from(
            $this->getBundlePriceTable(),
            [
                'entity_id',
                'customer_group_id',
                'website_id',
                'tax_class_id',
                'orig_price',
                'price',
                'min_price',
                'max_price',
                'tier_price',
            ]
        );

        $this->tableMaintainer->insertFromSelect($select, $priceTable->getTableName(), [
            "entity_id",
            "customer_group_id",
            "website_id",
            "tax_class_id",
            "price",
            "final_price",
            "min_price",
            "max_price",
            "tier_price",
        ]);
    }

    /**
     * Make insert/update bundle option price.
     *
     * @return void
     * @param IndexTableStructure $priceTable
     */
    private function applyBundleOptionPrice($priceTable): void
    {
        $connection = $this->getConnection();

        $subSelect = $connection->select()->from(
            $this->getBundleOptionTable(),
            [
                'entity_id',
                'customer_group_id',
                'website_id',
                'min_price' => new \Zend_Db_Expr('SUM(min_price)'),
                'alt_price' => new \Zend_Db_Expr('MIN(alt_price)'),
                'max_price' => new \Zend_Db_Expr('SUM(max_price)'),
                'tier_price' => new \Zend_Db_Expr('SUM(tier_price)'),
                'alt_tier_price' => new \Zend_Db_Expr('MIN(alt_tier_price)'),
            ]
        )->group(
            ['entity_id', 'customer_group_id', 'website_id']
        );

        $minPrice = 'i.min_price + ' . $connection->getIfNullSql('io.min_price', '0');
        $tierPrice = 'i.tier_price + ' . $connection->getIfNullSql('io.tier_price', '0');
        $select = $connection->select()->join(
            ['io' => $subSelect],
            'i.entity_id = io.entity_id AND i.customer_group_id = io.customer_group_id' .
                ' AND i.website_id = io.website_id',
            []
        )->columns(
            [
                'min_price' => $connection->getCheckSql("{$minPrice} = 0", 'io.alt_price', $minPrice),
                'max_price' => new \Zend_Db_Expr('io.max_price + i.max_price'),
                'tier_price' => $connection->getCheckSql("{$tierPrice} = 0", 'io.alt_tier_price', $tierPrice),
            ]
        );

        $query = $select->crossUpdateFromSelect(['i' => $priceTable->getTableName()]);
        $connection->query($query);
    }

    /**
     * Get main table
     *
     * @param array $dimensions
     * @return string
     */
    private function getMainTable($dimensions)
    {
        if ($this->fullReindexAction) {
            return $this->tableMaintainer->getMainReplicaTable($dimensions);
        }
        return $this->tableMaintainer->getMainTableByDimensions($dimensions);
    }

    /**
     * Get connection
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     * @throws \DomainException
     */
    private function getConnection(): \Magento\Framework\DB\Adapter\AdapterInterface
    {
        if ($this->connection === null) {
            $this->connection = $this->resource->getConnection($this->connectionName);
        }

        return $this->connection;
    }

    /**
     * Get table
     *
     * @param string $tableName
     * @return string
     */
    private function getTable($tableName)
    {
        return $this->resource->getTableName($tableName, $this->connectionName);
    }
}
