<?php
/**
 * Grouped Products Price Indexer Resource model
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProduct\Model\ResourceModel\Product\Indexer\Price;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Indexer\DimensionalIndexerInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Catalog\Model\Indexer\Product\Price\TableMaintainer;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\IndexTableStructureFactory;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\IndexTableStructure;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\PriceModifierInterface;
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
     * @var bool
     */
    private $fullReindexAction;

    /**
     * @param IndexTableStructureFactory $indexTableStructureFactory
     * @param TableMaintainer $tableMaintainer
     * @param MetadataPool $metadataPool
     * @param ResourceConnection $resource
     * @param string $connectionName
     * @param bool $fullReindexAction
     */
    public function __construct(
        IndexTableStructureFactory $indexTableStructureFactory,
        TableMaintainer $tableMaintainer,
        MetadataPool $metadataPool,
        ResourceConnection $resource,
        $connectionName = 'indexer',
        $fullReindexAction = false
    ) {
        $this->indexTableStructureFactory = $indexTableStructureFactory;
        $this->tableMaintainer = $tableMaintainer;
        $this->connectionName = $connectionName;
        $this->metadataPool = $metadataPool;
        $this->resource = $resource;
        $this->fullReindexAction = $fullReindexAction;
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
        $query = $this->_prepareGroupedProductPriceDataSelect($dimensions, iterator_to_array($entityIds))
            ->insertFromSelect($temporaryPriceTable->getTableName());
        $this->getConnection()->query($query);
    }

    /**
     * Prepare data index select for Grouped products prices
     * @param $dimensions
     * @param array $entityIds the parent entity ids limitation
     * @return \Magento\Framework\DB\Select
     */
    protected function _prepareGroupedProductPriceDataSelect($dimensions, $entityIds = null)
    {
        $connection = $this->getConnection();

        $taxClassId = $connection->getCheckSql('MIN(i.tax_class_id) IS NULL', '0', 'MIN(i.tax_class_id)');
        $minCheckSql = $connection->getCheckSql('e.required_options = 0', 'i.min_price', 0);
        $maxCheckSql = $connection->getCheckSql('e.required_options = 0', 'i.max_price', 0);
        $linkField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();

        $select = $connection->select()->from(['l' => $this->getTable('catalog_product_link')], []);
        $select->columns(
            [
                'entity_id' => new \Zend_Db_Expr('l.product_id'),
                'customer_group_id' => new \Zend_Db_Expr('i.customer_group_id'),
                'website_id' => new \Zend_Db_Expr('i.website_id'),
                'tax_class_id' => $taxClassId,
                'price' => new \Zend_Db_Expr('NULL'),
                'final_price' => new \Zend_Db_Expr('NULL'),
                'min_price' => new \Zend_Db_Expr('MIN(' . $minCheckSql . ')'),
                'max_price' => new \Zend_Db_Expr('MAX(' . $maxCheckSql . ')'),
                'tier_price' => new \Zend_Db_Expr('NULL'),
            ]
        );
        $select->join(['i' => $this->getMainTable($dimensions)], 'i.entity_id = l.linked_product_id', []);
        $select->join(['e' => $this->getTable('catalog_product_entity')], "e.$linkField = l.linked_product_id", []);
        $select->where('l.link_type_id = ?', Link::LINK_TYPE_GROUPED);
        $select->group(['l.product_id', 'i.customer_group_id', 'i.website_id']);

        if ($entityIds !== null) {
            $select->where('l.product_id IN(?)', $entityIds);
        }

        foreach ($dimensions as $dimension) {
            if ($dimension->getName() === WebsiteDimensionProvider::DIMENSION_NAME) {
                $select->where('i.website_id = ?', $dimension->getValue());
            }
            if ($dimension->getName() === CustomerGroupDimensionProvider::DIMENSION_NAME) {
                $select->where('i.customer_group_id = ?', $dimension->getValue());
            }
        }

        return $select;
    }

    private function getTable($tableName)
    {
        return $this->resource->getTableName($tableName, $this->connectionName);
    }

    /**
     * Get connection
     *
     * return \Magento\Framework\DB\Adapter\AdapterInterface
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
}
