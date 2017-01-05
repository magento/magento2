<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedSearch\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Catalog\Api\Data\CategoryInterface;

class Index extends AbstractDb
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * Index constructor.
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param MetadataPool $metadataPool
     * @param null $connectionName
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        MetadataPool $metadataPool,
        $connectionName = null
    ) {
        $this->storeManager = $storeManager;
        $this->metadataPool = $metadataPool;
        parent::__construct($context, $connectionName);
    }

    /**
     * Implementation of abstract construct
     * @return void
     */
    protected function _construct()
    {
    }

    /**
     * Return array of price data per customer and website by products
     *
     * @param null|array $productIds
     * @return array
     */
    protected function _getCatalogProductPriceData($productIds = null)
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from(
            $this->getTable('catalog_product_index_price'),
            ['entity_id', 'customer_group_id', 'website_id', 'min_price']
        );

        if ($productIds) {
            $select->where('entity_id IN (?)', $productIds);
        }

        $result = [];
        foreach ($connection->fetchAll($select) as $row) {
            $result[$row['website_id']][$row['entity_id']][$row['customer_group_id']] = round($row['min_price'], 2);
        }

        return $result;
    }

    /**
     * Retrieve price data for product
     *
     * @param null|array $productIds
     * @param int $storeId
     * @return array
     */
    public function getPriceIndexData($productIds, $storeId)
    {
        $priceProductsIndexData = $this->_getCatalogProductPriceData($productIds);

        $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();
        if (!isset($priceProductsIndexData[$websiteId])) {
            return [];
        }

        return $priceProductsIndexData[$websiteId];
    }

    /**
     * Prepare system index data for products.
     *
     * @param int $storeId
     * @param null|array $productIds
     * @return array
     */
    public function getCategoryProductIndexData($storeId = null, $productIds = null)
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from(
            [$this->getTable('catalog_category_product_index')],
            ['category_id', 'product_id', 'position', 'store_id']
        )->where(
            'store_id = ?',
            $storeId
        );

        if ($productIds) {
            $select->where('product_id IN (?)', $productIds);
        }

        $result = [];
        foreach ($connection->fetchAll($select) as $row) {
            $result[$row['product_id']][$row['category_id']] = $row['position'];
        }

        return $result;
    }

    /**
     * Retrieve moved categories product ids
     *
     * @param int $categoryId
     * @return array
     */
    public function getMovedCategoryProductIds($categoryId)
    {
        $connection = $this->getConnection();

        $identifierField = $this->metadataPool->getMetadata(CategoryInterface::class)->getIdentifierField();

        $select = $connection->select()->distinct()->from(
            ['c_p' => $this->getTable('catalog_category_product')],
            ['product_id']
        )->join(
            ['c_e' => $this->getTable('catalog_category_entity')],
            'c_p.category_id = c_e.' . $identifierField,
            []
        )->where(
            $connection->quoteInto('c_e.path LIKE ?', '%/' . $categoryId . '/%')
        )->orWhere(
            'c_p.category_id = ?',
            $categoryId
        );

        return $connection->fetchCol($select);
    }
}
