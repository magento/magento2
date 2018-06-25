<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Model\ResourceModel\Indexer;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Downloadable\Model\Product\Type;
use Magento\Eav\Model\Config;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Indexer\DimensionalIndexerInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Catalog\Model\Indexer\Product\Price\TableMaintainer;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\Query\BaseFinalPrice;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\IndexTableStructureFactory;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\IndexTableStructure;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\PriceModifierInterface;
use Magento\Store\Model\Indexer\WebsiteDimensionProvider;
use Magento\Customer\Model\Indexer\CustomerGroupDimensionProvider;

/**
 * Configurable Products Price Indexer Resource model
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
     * @var string
     */
    private $productType;

    /**
     * @var PriceModifierInterface[]
     */
    private $priceModifiers;
    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @param BaseFinalPrice $baseFinalPrice
     * @param IndexTableStructureFactory $indexTableStructureFactory
     * @param TableMaintainer $tableMaintainer
     * @param MetadataPool $metadataPool
     * @param Config $eavConfig
     * @param ResourceConnection $resource
     * @param string $connectionName
     * @param string $productType
     * @param array $priceModifiers
     */
    public function __construct(
        BaseFinalPrice $baseFinalPrice,
        IndexTableStructureFactory $indexTableStructureFactory,
        TableMaintainer $tableMaintainer,
        MetadataPool $metadataPool,
        Config $eavConfig,
        ResourceConnection $resource,
        $connectionName = 'indexer',
        $productType = Type::TYPE_DOWNLOADABLE,
        array $priceModifiers = []
    ) {
        $this->baseFinalPrice = $baseFinalPrice;
        $this->indexTableStructureFactory = $indexTableStructureFactory;
        $this->tableMaintainer = $tableMaintainer;
        $this->productType = $productType;
        $this->priceModifiers = $priceModifiers;
        $this->metadataPool = $metadataPool;
        $this->resource = $resource;
        $this->eavConfig = $eavConfig;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function executeByDimension(array $dimensions, \Traversable $entityIds = null)
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
        $this->applyPriceModifiers($temporaryPriceTable);
        $this->applyDownloadableLink($temporaryPriceTable, $dimensions);
    }

    /**
     * Apply price modifiers to temporary price index table
     *
     * @param IndexTableStructure $temporaryPriceTable
     * @return void
     */
    private function applyPriceModifiers(IndexTableStructure $temporaryPriceTable)
    {
        foreach ($this->priceModifiers as $priceModifier) {
            $priceModifier->modifyPrice($temporaryPriceTable);
        }
    }

    /**
     * Calculate and apply Downloadable links price to index
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
        $this->getConnection()->createTable(
        $this->getConnection()->createTableByDdl(
            $this->getTable('catalog_product_index_price_downlod_tmp'),
            $temporaryDownloadableTableName
        ));
        $this->fillTemporaryTable($temporaryDownloadableTableName, $dimensions);
        $this->updateTemporaryDownloadableTable($temporaryPriceTable->getTableName(), $temporaryDownloadableTableName);
        $this->getConnection()->delete($temporaryDownloadableTableName);
        return $this;
    }

    /**
     * Retrieve catalog_product attribute instance by attribute code
     * @param string $attributeCode
     * @return \Magento\Eav\Model\Entity\Attribute\AbstractAttribute
     */
    protected function getAttribute($attributeCode)
    {
        return $this->eavConfig->getAttribute(Product::ENTITY, $attributeCode);
    }

    /**
     * Put data into catalog product price indexer Downloadable links price  temp table
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

        foreach ($dimensions as $dimension) {
            if ($dimension->getName() === WebsiteDimensionProvider::DIMENSION_NAME) {
                $select->where('`i`.website_id = ?', $dimension->getValue());
            }
            if ($dimension->getName() === CustomerGroupDimensionProvider::DIMENSION_NAME) {
                $select->where('`i`.customer_group_id = ?', $dimension->getValue());
            }
        }

        $query = $select->insertFromSelect($temporaryDownloadableTableName);
        $this->getConnection()->query($query);
    }

    /**
     * Update data in the catalog product price indexer temp table
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

    /**
     * @param array $dimensions
     * @param \Traversable $entityIds
     * @param $temporaryPriceTable
     */
    private function fillFinalPrice(array $dimensions, \Traversable $entityIds, $temporaryPriceTable): void
    {
        $select = $this->baseFinalPrice->getQuery($dimensions, $this->productType, iterator_to_array($entityIds));
        $query = $select->insertFromSelect($temporaryPriceTable->getTableName(), [], false);
        $this->tableMaintainer->getConnection()->query($query);
    }
}
