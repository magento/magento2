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
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;

/**
 * Remove bundle product from price index when all products in required option are disabled
 */
class DisabledProductOptionPriceModifier implements PriceModifierInterface, ResetAfterRequestInterface
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
    private $websiteIdsOfProduct = [];

    /**
     * @var BundleSelection
     */
    private BundleSelection $bundleSelection;

    /**
     * @var Config
     */
    private Config $config;

    /**
     * @var MetadataPool
     */
    private MetadataPool $metadataPool;

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
        foreach ($this->getBundleIds($entityIds) as $entityId) {
            $entityId = (int) $entityId;
            foreach ($this->getWebsiteIdsOfProduct($entityId) as $websiteId) {
                $websiteId = (int) $websiteId;
                $productIdsDisabledRequired = $this->selectionProductsDisabledRequired
                    ->getChildProductIds($entityId, $websiteId);
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
        $this->websiteIdsOfProduct[$entityId] = $connection->fetchCol($select);

        return $this->websiteIdsOfProduct[$entityId];
    }

    /**
     * Get Bundle Ids
     *
     * @param array $entityIds
     * @return \Traversable
     */
    private function getBundleIds(array $entityIds): \Traversable
    {
        $connection = $this->resourceConnection->getConnection('indexer');
        $select = $connection->select();
        $select->from(
            ['cpe' => $this->resourceConnection->getTableName('catalog_product_entity')],
            ['entity_id']
        )->where('cpe.entity_id in ( ? )', !empty($entityIds) ? $entityIds : [0], \Zend_Db::INT_TYPE)
        ->where('type_id = ?', Type::TYPE_BUNDLE);

        $statement = $select->query();
        while ($id = $statement->fetchColumn()) {
            yield $id;
        }
    }

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        $this->websiteIdsOfProduct = [];
    }
}
