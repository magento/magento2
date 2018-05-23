<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\Query;

use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Framework\DB\Select;

/**
 * Prepare base select for Product Price index limited by specified dimensions: website and customer group
 */
class BaseFinalPrice
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @var string
     */
    private $connectionName;

    /**
     * @var JoinAttributeProcessor
     */
    private $joinAttributeProcessor;

    /**
     * BaseFinalPrice constructor.
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param JoinAttributeProcessor $joinAttributeProcessor
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        JoinAttributeProcessor $joinAttributeProcessor,
        $connectionName = 'indexer'
    ) {
        $this->resource = $resource;
        $this->connectionName = $connectionName;
        $this->joinAttributeProcessor = $joinAttributeProcessor;
    }

    /**
     * @param int $websiteId
     * @param int $customerGroupId
     * @param string $productType
     * @param array $entityIds
     * @return Select
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Db_Select_Exception
     */
    public function getQuery(int $websiteId, int $customerGroupId, string $productType, array $entityIds = []): Select
    {
        $connection = $this->resource->getConnection($this->connectionName);

        $select = $connection->select()->from(
            ['e' => $this->getTable('catalog_product_entity')],
            ['entity_id']
        )->joinInner(
            ['pw' => $this->getTable('catalog_product_website')],
            sprintf('pw.product_id = e.entity_id AND pw.website_id = %s', $websiteId),
            []
        )->joinInner(
            ['cg' => $this->getTable('customer_group')],
            sprintf('cg.customer_group_id = %s', $customerGroupId),
            ['customer_group_id']
        )->joinInner(
            ['cwd' => $this->getTable('catalog_product_index_website')], //website currency rates
            'pw.website_id = cwd.website_id',
            []
        )->joinLeft(
            ['tp' => $this->getTable('catalog_product_index_tier_price')], //TODO: alligig with tier price
            'tp.entity_id = e.entity_id AND tp.website_id = pw.website_id' .
            ' AND tp.customer_group_id = cg.customer_group_id',
            []
        );

        $this->joinAttributeProcessor->process($select, 'status', Status::STATUS_ENABLED);

        $price = $this->joinAttributeProcessor->process($select, $websiteId, 'price');
        $specialPrice = $this->joinAttributeProcessor->process($select, $websiteId, 'special_price');
        $specialFrom = $this->joinAttributeProcessor->process($select, $websiteId, 'special_from_date');
        $specialTo = $this->joinAttributeProcessor->process($select, $websiteId, 'special_to_date');
        $currentDate = 'cwd.website_date';

        $maxUnsignedBigint = '~0';
        $specialFromDate = $connection->getDatePartSql($specialFrom);
        $specialToDate = $connection->getDatePartSql($specialTo);
        $specialFromExpr = "{$specialFrom} IS NULL OR {$specialFromDate} <= {$currentDate}";
        $specialToExpr = "{$specialTo} IS NULL OR {$specialToDate} >= {$currentDate}";
        $specialPriceExpr = $connection->getCheckSql(
            "{$specialPrice} IS NOT NULL AND {$specialFromExpr} AND {$specialToExpr}",
            $specialPrice,
            $maxUnsignedBigint
        );
        $tierPrice = new \Zend_Db_Expr('tp.min_price');
        $tierPriceExpr = $connection->getIfNullSql(
            $tierPrice,
            $maxUnsignedBigint
        );
        $finalPrice = $connection->getLeastSql([
            $price,
            $specialPriceExpr,
            $tierPriceExpr,
        ]);

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

        $select->where(sprintf("e.type_id = '%s'", $productType));

        if ($entityIds !== null) {
            $select->where(sprintf('e.entity_id BETWEEN %s AND %s', min($entityIds), max($entityIds)));
        }

        return $select;
    }

    /**
     * @param string $tableName
     * @return string
     */
    private function getTable($tableName)
    {
        return $this->resource->getTableName($tableName, $this->connectionName);
    }
}
