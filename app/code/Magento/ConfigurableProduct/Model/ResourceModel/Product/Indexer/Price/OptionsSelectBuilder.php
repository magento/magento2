<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Model\ResourceModel\Product\Indexer\Price;

use Magento\Catalog\Model\ResourceModel\Product\BaseSelectProcessorInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\MetadataPool;

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
        $metadata = $this->metadataPool->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class);
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

        $select->columns(
            [
                'le.entity_id',
                'customer_group_id',
                'website_id',
                'min_price' => 'MIN(final_price)',
                'max_price' => 'MAX(final_price)',
                'tier_price' => 'MIN(tier_price)',
            ]
        )->group(
            ['le.entity_id', 'customer_group_id', 'website_id']
        );
        if ($entityIds !== null) {
            $select->where('le.entity_id IN (?)', $entityIds, \Zend_Db::INT_TYPE);
        }
        return $this->selectProcessor->process($select);
    }
}
