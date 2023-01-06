<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Model\Plugin;

use Magento\Bundle\Model\Product\Price;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\IndexTableStructure;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Model\Indexer\ProductPriceIndexFilter;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Checks if product is part of dynamic price bundle and skips price reindex
 */
class ProductPriceIndexModifier
{
    /**
     * @var StockConfigurationInterface
     */
    private StockConfigurationInterface $stockConfiguration;

    /**
     * @var ResourceConnection
     */
    private ResourceConnection $resourceConnection;

    /**
     * @var MetadataPool
     */
    private MetadataPool $metadataPool;

    /**
     * @var ProductRepositoryInterface
     */
    private ProductRepositoryInterface $productRepository;

    /**
     * @var string
     */
    private string $connectionName;

    /**
     * @param StockConfigurationInterface $stockConfiguration
     * @param ResourceConnection $resourceConnection
     * @param MetadataPool $metadataPool
     * @param ProductRepositoryInterface|null $productRepository
     * @param string $connectionName
     */
    public function __construct(
        StockConfigurationInterface $stockConfiguration,
        ResourceConnection $resourceConnection,
        MetadataPool $metadataPool,
        ?ProductRepositoryInterface $productRepository = null,
        string $connectionName = 'indexer'
    ) {
        $this->stockConfiguration = $stockConfiguration;
        $this->resourceConnection = $resourceConnection;
        $this->metadataPool = $metadataPool;
        $this->connectionName = $connectionName;
        $this->productRepository = $productRepository ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(ProductRepositoryInterface::class);
    }

    /**
     * Skip entity price index that are part of a dynamic price bundle
     *
     * @param ProductPriceIndexFilter $subject
     * @param callable $proceed
     * @param IndexTableStructure $priceTable
     * @param array $entityIds
     * @return void
     * @throws NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundModifyPrice(
        ProductPriceIndexFilter $subject,
        callable                $proceed,
        IndexTableStructure     $priceTable,
        array                   $entityIds = []
    ) {
        if (empty($entityIds) || $this->stockConfiguration->isShowOutOfStock()) {
            return $proceed($priceTable, $entityIds);
        }
        $filteredEntities = $this->filterProductsFromDynamicPriceBundle($priceTable->getTableName(), $entityIds);

        if (!empty($filteredEntities)) {
            $proceed($priceTable, $filteredEntities);
        }
    }

    /**
     * Filter products that are part of a dynamic bundle price configuration
     *
     * @param string $priceTableName
     * @param array $productIds
     * @return array
     * @throws NoSuchEntityException
     */
    private function filterProductsFromDynamicPriceBundle(string $priceTableName, array $productIds): array
    {
        $linkField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();
        $connection = $this->resourceConnection->getConnection($this->connectionName);
        $select = $connection->select();
        $select->from(['selection' => $this->resourceConnection->getTableName('catalog_product_bundle_selection')]);
        $select->columns(['product.entity_id AS bundle_id', 'selection.product_id AS child_product_id']);
        $select->joinInner(
            ['price' => $this->resourceConnection->getTableName($priceTableName)],
            implode(' AND ', ['price.entity_id = selection.product_id'])
        );
        $select->joinInner(
            ['product' => $this->resourceConnection->getTableName('catalog_product_entity')],
            "product.$linkField = selection.parent_product_id"
        );
        $select->where('selection.product_id IN (?)', $productIds);
        $select->where('product.type_id = ?', Type::TYPE_BUNDLE);
        $bundleProducts = $connection->fetchAll($select);

        if (empty($bundleProducts)) {
            return [];
        }

        $filteredProducts = [];
        foreach ($bundleProducts as $bundle) {
            $bundleProduct = $this->productRepository->getById($bundle['bundle_id']);
            if ($bundleProduct->getPriceType() != Price::PRICE_TYPE_DYNAMIC) {
                $filteredProducts[] = $bundle['child_product_id'];
            }
        }

        return $filteredProducts;
    }
}
