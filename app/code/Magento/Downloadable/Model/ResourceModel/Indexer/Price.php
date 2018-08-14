<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Model\ResourceModel\Indexer;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\BasePriceModifier;
use Magento\Downloadable\Model\Product\Type;
use Magento\Eav\Model\Config;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Indexer\DimensionalIndexerInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Catalog\Model\Indexer\Product\Price\TableMaintainer;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\Query\BaseFinalPrice;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\IndexTableStructureFactory;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\IndexTableStructure;

/**
 * Downloadable Product Price Indexer Resource model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Price implements DimensionalIndexerInterface
{
    /**
     * @var BaseFinalPrice
     */
    private $baseFinalPrice;

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
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;

    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @var BasePriceModifier
     */
    private $basePriceModifier;

    /**
     * @param BaseFinalPrice $baseFinalPrice
     * @param IndexTableStructureFactory $indexTableStructureFactory
     * @param TableMaintainer $tableMaintainer
     * @param MetadataPool $metadataPool
     * @param Config $eavConfig
     * @param ResourceConnection $resource
     * @param BasePriceModifier $basePriceModifier
     * @param string $connectionName
     */
    public function __construct(
        BaseFinalPrice $baseFinalPrice,
        IndexTableStructureFactory $indexTableStructureFactory,
        TableMaintainer $tableMaintainer,
        MetadataPool $metadataPool,
        Config $eavConfig,
        ResourceConnection $resource,
        BasePriceModifier $basePriceModifier,
        $connectionName = 'indexer'
    ) {
        $this->baseFinalPrice = $baseFinalPrice;
        $this->indexTableStructureFactory = $indexTableStructureFactory;
        $this->tableMaintainer = $tableMaintainer;
        $this->connectionName = $connectionName;
        $this->metadataPool = $metadataPool;
        $this->resource = $resource;
        $this->eavConfig = $eavConfig;
        $this->basePriceModifier = $basePriceModifier;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function executeByDimensions(array $dimensions, \Traversable $entityIds)
    {
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
        $this->fillFinalPrice($dimensions, $entityIds, $temporaryPriceTable);
        $this->basePriceModifier->modifyPrice($temporaryPriceTable, iterator_to_array($entityIds));
        $this->applyDownloadableLink($temporaryPriceTable, $dimensions);
    }

    /**
     * Calculate and apply Downloadable links price to index
     *
     * @param IndexTableStructure $temporaryPriceTable
     * @param array $dimensions
     * @return $this
     * @throws \Exception
     */
    private function applyDownloadableLink(
        IndexTableStructure $temporaryPriceTable,
        array $dimensions
    ) {
        $temporaryDownloadableTableName = 'catalog_product_index_price_downlod_temp';
        $this->getConnection()->createTemporaryTableLike(
            $temporaryDownloadableTableName,
            $this->getTable('catalog_product_index_price_downlod_tmp'),
            true
        );
        $this->fillTemporaryTable($temporaryDownloadableTableName, $dimensions);
        $this->updateTemporaryDownloadableTable($temporaryPriceTable->getTableName(), $temporaryDownloadableTableName);
        $this->getConnection()->delete($temporaryDownloadableTableName);
        return $this;
    }

    /**
     * Retrieve catalog_product attribute instance by attribute code
     *
     * @param string $attributeCode
     * @return \Magento\Eav\Model\Entity\Attribute\AbstractAttribute
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getAttribute($attributeCode)
    {
        return $this->eavConfig->getAttribute(Product::ENTITY, $attributeCode);
    }

    /**
     * Put data into catalog product price indexer Downloadable links price  temp table
     *
     * @param string $temporaryDownloadableTableName
     * @param array $dimensions
     * @return void
     * @throws \Exception
     */
    private function fillTemporaryTable(string $temporaryDownloadableTableName, array $dimensions)
    {
        $dlType = $this->getAttribute('links_purchased_separately');
        $ifPrice = $this->getConnection()->getIfNullSql('dlpw.price_id', 'dlpd.price');
        $metadata = $this->metadataPool->getMetadata(ProductInterface::class);
        $linkField = $metadata->getLinkField();

        $select = $this->getConnection()->select()->from(
            ['i' => $this->tableMaintainer->getMainTmpTable($dimensions)],
            ['entity_id', 'customer_group_id', 'website_id']
        )->join(
            ['dl' => $dlType->getBackend()->getTable()],
            "dl.{$linkField} = i.entity_id AND dl.attribute_id = {$dlType->getAttributeId()}" . " AND dl.store_id = 0",
            []
        )->join(
            ['dll' => $this->getTable('downloadable_link')],
            'dll.product_id = i.entity_id',
            []
        )->join(
            ['dlpd' => $this->getTable('downloadable_link_price')],
            'dll.link_id = dlpd.link_id AND dlpd.website_id = 0',
            []
        )->joinLeft(
            ['dlpw' => $this->getTable('downloadable_link_price')],
            'dlpd.link_id = dlpw.link_id AND dlpw.website_id = i.website_id',
            []
        )->where(
            'dl.value = ?',
            1
        )->group(
            ['i.entity_id', 'i.customer_group_id', 'i.website_id']
        )->columns(
            [
                'min_price' => new \Zend_Db_Expr('MIN(' . $ifPrice . ')'),
                'max_price' => new \Zend_Db_Expr('SUM(' . $ifPrice . ')'),
            ]
        );
        $query = $select->insertFromSelect($temporaryDownloadableTableName);
        $this->getConnection()->query($query);
    }

    /**
     * Update data in the catalog product price indexer temp table
     *
     * @param string $temporaryPriceTableName
     * @param string $temporaryDownloadableTableName
     * @return void
     */
    private function updateTemporaryDownloadableTable(
        string $temporaryPriceTableName,
        string $temporaryDownloadableTableName
    ) {
        $ifTierPrice = $this->getConnection()->getCheckSql(
            'i.tier_price IS NOT NULL',
            '(i.tier_price + id.min_price)',
            'NULL'
        );

        $selectForCrossUpdate = $this->getConnection()->select()->join(
            ['id' => $temporaryDownloadableTableName],
            'i.entity_id = id.entity_id AND i.customer_group_id = id.customer_group_id' .
            ' AND i.website_id = id.website_id',
            []
        );
        // adds price of custom option, that was applied in DefaultPrice::_applyCustomOption
        $selectForCrossUpdate->columns(
            [
                'min_price' => new \Zend_Db_Expr('i.min_price + id.min_price'),
                'max_price' => new \Zend_Db_Expr('i.max_price + id.max_price'),
                'tier_price' => new \Zend_Db_Expr($ifTierPrice),
            ]
        );
        $query = $selectForCrossUpdate->crossUpdateFromSelect(['i' => $temporaryPriceTableName]);
        $this->getConnection()->query($query);
    }

    /**
     * Fill final price
     *
     * @param array $dimensions
     * @param \Traversable $entityIds
     * @param IndexTableStructure $temporaryPriceTable
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Db_Select_Exception
     */
    private function fillFinalPrice(
        array $dimensions,
        \Traversable $entityIds,
        IndexTableStructure $temporaryPriceTable
    ) {
        $select = $this->baseFinalPrice->getQuery($dimensions, Type::TYPE_DOWNLOADABLE, iterator_to_array($entityIds));
        $query = $select->insertFromSelect($temporaryPriceTable->getTableName(), [], false);
        $this->tableMaintainer->getConnection()->query($query);
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
