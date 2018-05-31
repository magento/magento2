<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\Query;

use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Customer\Model\Indexer\MultiDimensional\CustomerGroupDataProvider;
use Magento\Framework\DB\Select;
use Magento\Framework\DB\Sql\ColumnValueExpression;
use Magento\Framework\Exception\InputException;
use Magento\Store\Model\Indexer\MultiDimensional\WebsiteDataProvider;

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
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * BaseFinalPrice constructor.
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param JoinAttributeProcessor $joinAttributeProcessor
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        JoinAttributeProcessor $joinAttributeProcessor,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        $connectionName = 'indexer'
    ) {
        $this->resource = $resource;
        $this->connectionName = $connectionName;
        $this->joinAttributeProcessor = $joinAttributeProcessor;
        $this->moduleManager = $moduleManager;
        $this->eventManager = $eventManager;
    }

    /**
     * @param array $dimensions
     * @param string $productType
     * @param array $entityIds
     * @return Select
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Db_Select_Exception
     */
    public function getQuery(array $dimensions, string $productType, array $entityIds = []): Select
    {
        if (!isset($dimensions[WebsiteDataProvider::DIMENSION_NAME],
            $dimensions[CustomerGroupDataProvider::DIMENSION_NAME])
        ) {
            throw new InputException(__('All dimensions for product index price must be provided'));
        }
        $websiteId = $dimensions[WebsiteDataProvider::DIMENSION_NAME]->getValue();
        $customerGroupId = $dimensions[CustomerGroupDataProvider::DIMENSION_NAME]->getValue();
        $connection = $this->resource->getConnection($this->connectionName);

        $select = $connection->select()->from(
            ['e' => $this->getTable('catalog_product_entity')],
            ['entity_id']
        )->joinInner(
            ['cg' => $this->getTable('customer_group')],
            sprintf('cg.customer_group_id = %s', $customerGroupId),
            ['customer_group_id']
        )->joinInner(
            ['pw' => $this->getTable('catalog_product_website')],
            sprintf('pw.product_id = e.entity_id AND pw.website_id = %s', $websiteId),
            ['pw.website_id']
        )->joinInner(
            ['cwd' => $this->getTable('catalog_product_index_website')],
            'pw.website_id = cwd.website_id',
            []
        )->joinLeft(
            ['tp' => $this->getTable('catalog_product_index_tier_price')], //TODO: alligig with tier price
            'tp.entity_id = e.entity_id AND tp.website_id = pw.website_id' .
            ' AND tp.customer_group_id = cg.customer_group_id',
            []
        );

        if ($this->moduleManager->isEnabled('Magento_Tax')) {
            $taxClassId = $this->joinAttributeProcessor->process($select,'tax_class_id');
        } else {
            $taxClassId = new \Zend_Db_Expr(0);
        }
        $select->columns(['tax_class_id' => $taxClassId]);

        $this->joinAttributeProcessor->process($select, 'status', Status::STATUS_ENABLED);

        $price = $this->joinAttributeProcessor->process($select, 'price');
        $specialPrice = $this->joinAttributeProcessor->process($select, 'special_price');
        $specialFrom = $this->joinAttributeProcessor->process($select, 'special_from_date');
        $specialTo = $this->joinAttributeProcessor->process($select, 'special_to_date');
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
        $tierPrice = new ColumnValueExpression('tp.min_price');
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
                'price' => $connection->getIfNullSql($price, 0), //orig_price in catalog_product_index_price_final_tmp
                'final_price' => $connection->getIfNullSql($finalPrice, 0), //price in catalog_product_index_price_final_tmp
                'min_price' => $connection->getIfNullSql($finalPrice, 0),
                'max_price' => $connection->getIfNullSql($finalPrice, 0),
                'tier_price' => $tierPrice,
            ]
        );

        $select->where(sprintf("e.type_id = '%s'", $productType));

        if ($entityIds !== null) {
            $select->where(sprintf('e.entity_id BETWEEN %s AND %s', min($entityIds), max($entityIds)));
        }

        /**
         * throw event for backward compatibility
         */
        $this->eventManager->dispatch(
            'prepare_catalog_product_index_select',
            [
                'select' => $select,
                'entity_field' => new ColumnValueExpression('e.entity_id'),
                'website_field' => new ColumnValueExpression('pw.website_id'),
                'store_field' => new ColumnValueExpression('cwd.default_store_id'),
                'website_id' => new ColumnValueExpression($websiteId),
                'customer_group_id' => new ColumnValueExpression($customerGroupId),
            ]
        );

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
