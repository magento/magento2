<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Model\ResourceModel\Product\Indexer\Price;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product\BaseSelectProcessorInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\MetadataPool;
use Zend_Db;

/**
 * Build select for aggregating configurable product options prices
 */
class OptionsSelectBuilder implements OptionsSelectBuilderInterface
{
    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var string
     */
    private $connectionName;

    /**
     * @var BaseSelectProcessorInterface
     */
    private $selectProcessor;

    /**
     * @param BaseSelectProcessorInterface $selectProcessor
     * @param MetadataPool $metadataPool
     * @param ResourceConnection $resourceConnection
     * @param string $connectionName
     */
    public function __construct(
        BaseSelectProcessorInterface $selectProcessor,
        MetadataPool $metadataPool,
        ResourceConnection $resourceConnection,
        string $connectionName = 'indexer'
    ) {
        $this->selectProcessor = $selectProcessor;
        $this->metadataPool = $metadataPool;
        $this->resourceConnection = $resourceConnection;
        $this->connectionName = $connectionName;
    }

    /**
     * @inheritdoc
     */
    public function execute(string $indexTable, ?array $entityIds = null): Select
    {
        $connection = $this->resourceConnection->getConnection($this->connectionName);
        $metadata = $this->metadataPool->getMetadata(ProductInterface::class);
        $linkField = $metadata->getLinkField();

        $select = $connection->select()
            ->from(
                ['i' => $indexTable],
                []
            )
            ->join(
                ['l' => $this->resourceConnection->getTableName('catalog_product_super_link', $this->connectionName)],
                'l.product_id = i.entity_id',
                []
            )
            ->join(
                ['le' => $this->resourceConnection->getTableName('catalog_product_entity', $this->connectionName)],
                'le.' . $linkField . ' = l.parent_id',
                []
            );
        if ($entityIds !== null) {
            $select->where('le.entity_id IN (?)', $entityIds, Zend_Db::INT_TYPE);
        }

        // Retrieve minimal final_price value from all configurable product options
        $subSelectFinalPrice = clone $select;
        $subSelectFinalPrice->reset(Select::COLUMNS);
        $subSelectFinalPrice->reset(Select::GROUP);
        $subSelectFinalPrice->columns(
            [
                'le.entity_id',
                'i.customer_group_id',
                'i.website_id',
                'final_price' => 'MIN(i.final_price)'
            ]
        )->group(
            ['le.entity_id', 'i.customer_group_id', 'i.website_id']
        );

        // Retrieve regular price for the minimal final_price value
        $subSelectPrice = clone $select;
        $subSelectPrice->reset(Select::COLUMNS);
        $subSelectPrice->reset(Select::GROUP);
        $subSelectPrice->columns(
            [
                'le.entity_id',
                'i.customer_group_id',
                'i.website_id',
                'i.price',
                'i.final_price'
            ]
        )->group(
            ['le.entity_id', 'i.customer_group_id', 'i.website_id', 'i.final_price']
        );

        $select->joinInner(
            ['i_final_price' => $subSelectFinalPrice],
            'i_final_price.website_id = i.website_id AND ' .
            'i_final_price.customer_group_id = i.customer_group_id AND ' .
            'i_final_price.entity_id = le.entity_id',
            []
        );
        $select->joinInner(
            ['i_price' => $subSelectPrice],
            'i_price.final_price = i_final_price.final_price AND ' .
            'i_price.website_id = i.website_id AND ' .
            'i_price.customer_group_id = i.customer_group_id AND ' .
            'i_price.entity_id = le.entity_id',
            []
        );

        $select->columns(
            [
                'le.entity_id',
                'i.customer_group_id',
                'i.website_id',
                'min_price' => 'i_final_price.final_price',
                'max_price' => 'MAX(i.final_price)',
                'tier_price' => 'MIN(i.tier_price)',
                'price' => 'i_price.price',
                'final_price' => 'i_final_price.final_price',
            ]
        )->group(
            ['le.entity_id', 'i.customer_group_id', 'i.website_id']
        );

        return $this->selectProcessor->process($select);
    }
}
