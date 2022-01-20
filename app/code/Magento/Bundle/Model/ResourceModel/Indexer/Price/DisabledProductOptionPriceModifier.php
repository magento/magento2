<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Model\ResourceModel\Indexer\Price;

use Magento\Bundle\Model\Product\SelectionProductsDisabledRequired;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\IndexTableStructure;
use Magento\Framework\App\ResourceConnection;
use Magento\Catalog\Model\Config;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\PriceModifierInterface;
use Magento\Bundle\Model\ResourceModel\Selection as BundleSelection;

/**
 * Remove bundle product from price index when all products in required option are disabled
 */
class DisabledProductOptionPriceModifier implements PriceModifierInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var SelectionProductsDisabledRequired
     */
    private $selectionProductsDisabledRequired;

    /**
     * @var array
     */
    private $isBundle = [];

    /**
     * @var array
     */
    private $websiteIdsOfProduct = [];

    /**
     * @param ResourceConnection $resourceConnection
     * @param Config $config
     * @param MetadataPool $metadataPool
     * @param BundleSelection $bundleSelection
     * @param SelectionProductsDisabledRequired $selectionProductsDisabledRequired
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        Config $config,
        MetadataPool $metadataPool,
        BundleSelection $bundleSelection,
        SelectionProductsDisabledRequired $selectionProductsDisabledRequired
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->config = $config;
        $this->metadataPool = $metadataPool;
        $this->bundleSelection = $bundleSelection;
        $this->selectionProductsDisabledRequired = $selectionProductsDisabledRequired;
    }

    /**
     * Remove bundle product from price index when all products in required option are disabled
     *
     * @param IndexTableStructure $priceTable
     * @param array $entityIds
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function modifyPrice(IndexTableStructure $priceTable, array $entityIds = []) : void
    {
        foreach ($entityIds as $entityId) {
            $entityId = (int) $entityId;
            if (!$this->isBundle($entityId)) {
                continue;
            }
            foreach ($this->getWebsiteIdsOfProduct($entityId) as $websiteId) {
                $productIdsDisabledRequired = $this->selectionProductsDisabledRequired
                    ->getChildProductIds($entityId, (int)$websiteId);
                if ($productIdsDisabledRequired) {
                    $connection = $this->resourceConnection->getConnection('indexer');
                    $select = $connection->select();
                    $select->from(['price_index' => $priceTable->getTableName()], []);
                    $priceEntityField = $priceTable->getEntityField();
                    $select->where('price_index.website_id = ?', $websiteId);
                    $select->where("price_index.{$priceEntityField} = ?", $entityId);
                    $query = $select->deleteFromSelect('price_index');
                    $connection->query($query);
                }
            }
        }
    }

    /**
     * Get all website ids of product
     *
     * @param int $entityId
     * @return array
     */
    private function getWebsiteIdsOfProduct(int $entityId): array
    {
        if (isset($this->websiteIdsOfProduct[$entityId])) {
            return $this->websiteIdsOfProduct[$entityId];
        }
        $connection = $this->resourceConnection->getConnection('indexer');
        $select = $connection->select();
        $select->from(
            ['product_in_websites' => $this->resourceConnection->getTableName('catalog_product_website')],
            ['website_id']
        )->where('product_in_websites.product_id = ?', $entityId);
        foreach ($connection->fetchCol($select) as $websiteId) {
            $this->websiteIdsOfProduct[$entityId][] = (int)$websiteId;
        }
        return $this->websiteIdsOfProduct[$entityId];
    }

    /**
     * Is product bundle
     *
     * @param int $entityId
     * @return bool
     */
    private function isBundle(int $entityId): bool
    {
        if (isset($this->isBundle[$entityId])) {
            return $this->isBundle[$entityId];
        }
        $connection = $this->resourceConnection->getConnection('indexer');
        $select = $connection->select();
        $select->from(
            ['cpe' => $this->resourceConnection->getTableName('catalog_product_entity')],
            ['type_id']
        )->where('cpe.entity_id = ?', $entityId);
        $typeId = $connection->fetchOne($select);
        $this->isBundle[$entityId] = $typeId === Type::TYPE_BUNDLE;
        return $this->isBundle[$entityId];
    }
}
