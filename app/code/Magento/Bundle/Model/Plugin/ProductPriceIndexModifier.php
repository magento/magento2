<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Model\Plugin;

use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\IndexTableStructure;
use Magento\CatalogInventory\Model\Indexer\ProductPriceIndexFilter;
use Magento\Framework\App\ResourceConnection;

/**
 * Checks if product is part of dynamic price bundle and skips price reindex
 */
class ProductPriceIndexModifier
{
    /**
     * @var ResourceConnection
     */
    private ResourceConnection $resourceConnection;

    /**
     * @var string|null
     */
    private ?string $connectionName;

    /**
     * @param ResourceConnection $resourceConnection
     * @param string $connectionName
     */
    public function __construct(ResourceConnection $resourceConnection, string $connectionName = 'indexer')
    {
        $this->resourceConnection = $resourceConnection;
        $this->connectionName = $connectionName;
    }

    /**
     * Skip entity price index that are part of a dynamic price bundle
     *
     * @param ProductPriceIndexFilter $subject
     * @param callable $proceed
     * @param IndexTableStructure $priceTable
     * @param array $entityIds
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundModifyPrice(
        ProductPriceIndexFilter $subject,
        callable                $proceed,
        IndexTableStructure     $priceTable,
        array                   $entityIds = []
    ) {
        if (empty($entityIds)) {
            $proceed($priceTable, []);
        }

        foreach ($entityIds as $id) {
            if (!$this->isWithinDynamicPriceBundle($priceTable->getTableName(), $id)) {
                $proceed($priceTable, [$id]);
            }
        }
    }

    /**
     * Check if the product is part of a dynamic price bundle configuration
     *
     * @param string $priceTableName
     * @param int $productId
     * @return bool
     */
    private function isWithinDynamicPriceBundle(string $priceTableName, int $productId): bool
    {
        $connection = $this->resourceConnection->getConnection($this->connectionName);
        $select = $connection->select();
        $select->from(['selection' => 'catalog_product_bundle_selection'], 'selection_id');
        $select->joinInner(
            ['entity' => 'catalog_product_entity'],
            implode(' AND ', ['selection.parent_product_id = entity.row_id']),
            null
        );
        $select->joinInner(
            ['price' => $priceTableName],
            implode(' AND ', ['price.entity_id = selection.product_id']),
            null
        );
        $select->where('selection.product_id = ?', $productId);
        $select->where('entity.type_id = ?', \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE);
        $select->where('price.tax_class_id = ?', \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC);

        return (int)$connection->fetchOne($select) != 0;
    }
}
