<?php
/**
 * Grouped Products Price Indexer Resource model
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProduct\Model\ResourceModel\Product\Indexer\Price;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\BasePriceModifier;
use Magento\Eav\Model\Config;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Indexer\DimensionalIndexerInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Catalog\Model\Indexer\Product\Price\TableMaintainer;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\Query\BaseFinalPrice;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\IndexTableStructureFactory;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\IndexTableStructure;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\PriceModifierInterface;
use Magento\GroupedProduct\Model\Product\Type\Grouped as GroupedType;
use Magento\GroupedProduct\Model\ResourceModel\Product\Link;
use Magento\Store\Model\Indexer\WebsiteDimensionProvider;
use Magento\Customer\Model\Indexer\CustomerGroupDimensionProvider;

/**
 * Calculate minimal and maximal prices for Grouped products
 * Use calculated price for relation products
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Grouped implements DimensionalIndexerInterface
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
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var string
     */
    private $connectionName;

    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @var PriceModifierInterface[]
     */
    private $priceModifiers;

    /**
     * @var bool
     */
    private $fullReindexAction;
    /**
     * @var BasePriceModifier
     */
    private $basePriceModifier;

    /**
     * @param IndexTableStructureFactory $indexTableStructureFactory
     * @param TableMaintainer $tableMaintainer
     * @param MetadataPool $metadataPool
     * @param ResourceConnection $resource
     * @param BasePriceModifier $basePriceModifier
     * @param string $connectionName
     * @param bool $fullReindexAction
     * @param array $priceModifiers
     */
    public function __construct(
        IndexTableStructureFactory $indexTableStructureFactory,
        TableMaintainer $tableMaintainer,
        MetadataPool $metadataPool,
        ResourceConnection $resource,
        BasePriceModifier $basePriceModifier,
        $connectionName = 'indexer',
        $fullReindexAction = false,
        array $priceModifiers = []
    ) {
        $this->indexTableStructureFactory = $indexTableStructureFactory;
        $this->tableMaintainer = $tableMaintainer;
        $this->connectionName = $connectionName;
        $this->priceModifiers = $priceModifiers;
        $this->metadataPool = $metadataPool;
        $this->resource = $resource;
        $this->fullReindexAction = $fullReindexAction;
        $this->connection = $this->resource->getConnection($this->connectionName);
        $this->basePriceModifier = $basePriceModifier;
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
        return $this->tableMaintainer->getMainTable($dimensions);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function executeByDimension(array $dimensions, \Traversable $entityIds = null)
    {
        /** @var IndexTableStructure $temporaryPriceTable */
        $temporaryPriceTable = $this->indexTableStructureFactory->create([
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
        ]);
        $this->basePriceModifier->modifyPrice($temporaryPriceTable, iterator_to_array($entityIds));
        $query = $this->_prepareGroupedProductPriceDataSelect($dimensions, iterator_to_array($entityIds))
            ->insertFromSelect($temporaryPriceTable->getTableName());
        $this->connection->query($query);
    }

    /**
     * Prepare data index select for Grouped products prices
     * @param $dimensions
     * @param int|array $entityIds the parent entity ids limitation
     * @return \Magento\Framework\DB\Select
     */
    protected function _prepareGroupedProductPriceDataSelect($dimensions, $entityIds = null)
    {
        $select = $this->connection->select();

        $select->from(
            ['e' => $this->getTable('catalog_product_entity')],
            'entity_id'
        );

        $linkField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();
        $select->joinLeft(
            ['l' => $this->getTable('catalog_product_link')],
            'e.' . $linkField . ' = l.product_id AND l.link_type_id=' . Link::LINK_TYPE_GROUPED,
            []
        );
        //aditional infromation about inner products
        $select->joinLeft(
            ['le' => $this->getTable('catalog_product_entity')],
            'le.entity_id = l.linked_product_id',
            []
        );
        $select->columns(
            [
                'i.customer_group_id',
                'i.website_id',
            ]
        );
        $taxClassId = $this->connection->getCheckSql('MIN(i.tax_class_id) IS NULL', '0', 'MIN(i.tax_class_id)');
        $minCheckSql = $this->connection->getCheckSql('le.required_options = 0', 'i.min_price', 0);
        $maxCheckSql = $this->connection->getCheckSql('le.required_options = 0', 'i.max_price', 0);
        $select->joinLeft(
            ['i' => $this->getMainTable($dimensions)],
            'i.entity_id = l.linked_product_id',
            [
                'tax_class_id' => $taxClassId,
                'price' => new \Zend_Db_Expr('NULL'),
                'final_price' => new \Zend_Db_Expr('NULL'),
                'min_price' => new \Zend_Db_Expr('MIN(' . $minCheckSql . ')'),
                'max_price' => new \Zend_Db_Expr('MAX(' . $maxCheckSql . ')'),
                'tier_price' => new \Zend_Db_Expr('NULL'),
            ]
        );
        $select->group(
            ['e.entity_id', 'i.customer_group_id', 'i.website_id']
        );
        $select->where(
            'e.type_id=?',
            GroupedType::TYPE_CODE
        );

        if ($entityIds !== null) {
            $select->where('e.entity_id IN(?)', $entityIds);
        }

        foreach ($dimensions as $dimension) {
            if ($dimension->getName() === WebsiteDimensionProvider::DIMENSION_NAME) {
                $select->where('`i`.website_id = ?', $dimension->getValue());
            }
            if ($dimension->getName() === CustomerGroupDimensionProvider::DIMENSION_NAME) {
                $select->where('`i`.customer_group_id = ?', $dimension->getValue());
            }
        }

        return $select;
    }

    private function getTable($tableName)
    {
        return $this->resource->getTableName($tableName, $this->connectionName);
    }
}
