<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Model\Indexer;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\CatalogRule\Model\Indexer\IndexerTableSwapperInterface as TableSwapper;
use Magento\Catalog\Model\ResourceModel\Indexer\ActiveTableSwitcher;
use Magento\Eav\Model\Config;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Store\Model\Group;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Zend_Db_Statement_Interface;

/**
 * Build select for rule relation with product.
 */
class RuleProductsSelectBuilder
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var ActiveTableSwitcher
     */
    private $activeTableSwitcher;

    /**
     * @var TableSwapper
     */
    private $tableSwapper;

    /**
     * @param ResourceConnection $resource
     * @param Config $eavConfig
     * @param StoreManagerInterface $storeManager
     * @param MetadataPool $metadataPool
     * @param ActiveTableSwitcher $activeTableSwitcher
     * @param TableSwapper|null $tableSwapper
     */
    public function __construct(
        ResourceConnection $resource,
        Config $eavConfig,
        StoreManagerInterface $storeManager,
        MetadataPool $metadataPool,
        ActiveTableSwitcher $activeTableSwitcher,
        ?TableSwapper $tableSwapper = null
    ) {
        $this->eavConfig = $eavConfig;
        $this->storeManager = $storeManager;
        $this->metadataPool = $metadataPool;
        $this->resource = $resource;
        $this->activeTableSwitcher = $activeTableSwitcher;
        $this->tableSwapper = $tableSwapper ??
            ObjectManager::getInstance()->get(TableSwapper::class);
    }

    /**
     * Build multiple products select for indexer according passed parameters
     *
     * @param int $websiteId
     * @param array $productIds
     * @param bool $useAdditionalTable
     * @return Zend_Db_Statement_Interface
     */
    public function buildSelect(
        int $websiteId,
        array $productIds,
        bool $useAdditionalTable = false
    ): Zend_Db_Statement_Interface {
        $connection = $this->resource->getConnection();
        $indexTable = $this->resource->getTableName('catalogrule_product');
        if ($useAdditionalTable) {
            $indexTable = $this->resource->getTableName(
                $this->tableSwapper->getWorkingTableName('catalogrule_product')
            );
        }

        /**
         * Sort order is important
         * It used for check stop price rule condition.
         * website_id   customer_group_id   product_id  sort_order
         *  1           1                   1           0
         *  1           1                   1           1
         *  1           1                   1           2
         * if row with sort order 1 will have stop flag we should exclude
         * all next rows for same product id from price calculation
         */
        $select = $connection->select()->from(
            ['rp' => $indexTable]
        )->order(
            ['rp.website_id', 'rp.customer_group_id', 'rp.product_id', 'rp.sort_order', 'rp.rule_id']
        );

        if (!empty($productIds)) {
            $select->where('rp.product_id IN (?)', $productIds);
        }

        /**
         * Join default price and websites prices to result
         */
        $priceAttr = $this->eavConfig->getAttribute(Product::ENTITY, 'price');
        $priceTable = $priceAttr->getBackend()->getTable();
        $attributeId = $priceAttr->getId();

        $linkField = $this->metadataPool
            ->getMetadata(ProductInterface::class)
            ->getLinkField();
        $select->join(
            ['e' => $this->resource->getTableName('catalog_product_entity')],
            sprintf('e.entity_id = rp.product_id'),
            []
        );
        $joinCondition = '%1$s.' . $linkField . '=e.' . $linkField . ' AND (%1$s.attribute_id='
            . $attributeId
            . ') and %1$s.store_id=%2$s';

        $select->join(
            ['pp_default' => $priceTable],
            sprintf($joinCondition, 'pp_default', Store::DEFAULT_STORE_ID),
            []
        );

        $website = $this->storeManager->getWebsite($websiteId);
        $defaultGroup = $website->getDefaultGroup();
        if ($defaultGroup instanceof Group) {
            $storeId = $defaultGroup->getDefaultStoreId();
        } else {
            $storeId = Store::DEFAULT_STORE_ID;
        }

        $select->joinInner(
            ['product_website' => $this->resource->getTableName('catalog_product_website')],
            'product_website.product_id=rp.product_id '
            . 'AND product_website.website_id = rp.website_id '
            . 'AND product_website.website_id='
            . $websiteId,
            []
        );

        $tableAlias = 'pp' . $websiteId;
        $select->joinLeft(
            [$tableAlias => $priceTable],
            sprintf($joinCondition, $tableAlias, $storeId),
            []
        );
        $select->columns(
            [
                'default_price' => $connection->getIfNullSql($tableAlias . '.value', 'pp_default.value'),
            ]
        );

        return $connection->query($select);
    }

    /**
     * Build select for indexer according passed parameters.
     *
     * @param int $websiteId
     * @param int|null $productId
     * @param bool $useAdditionalTable
     * @return Zend_Db_Statement_Interface
     */
    public function build(int $websiteId, ?int $productId = null, bool $useAdditionalTable = false)
    {
        return $this->buildSelect($websiteId, $productId === null ? [] : [$productId], $useAdditionalTable);
    }
}
