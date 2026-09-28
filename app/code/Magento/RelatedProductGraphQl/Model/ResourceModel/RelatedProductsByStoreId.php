<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\RelatedProductGraphQl\Model\ResourceModel;

use Magento\Catalog\Model\ResourceModel\Product\Link;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Processing db operations for retrieving related products by storeId
 */
class RelatedProductsByStoreId
{
    /**
     * @var Link
     */
    private $productLink;

    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @param Link $productLink
     * @param ResourceConnection $resource
     */
    public function __construct(
        Link $productLink,
        ResourceConnection $resource,
    ) {
        $this->productLink = $productLink;
        $this->connection = $resource->getConnection();
    }

    /**
     * Get Product Models by storeId
     *
     * @param array $linkedProductIds
     * @param string $websiteId
     * @return array
     * @throws LocalizedException
     */
    public function execute(array $linkedProductIds, string $websiteId): array
    {
        $linkedStoreProductIds = [];
        $mainTable = $this->productLink->getMainTable();
        $catalogProductWebsite = $this->productLink->getTable('catalog_product_website');
        if (!empty($linkedProductIds)) {
            $select = $this->connection->select();
            $select->from(
                ['main_table' => $mainTable],
                ['linked_product_id']
            )->join(
                ['product_website' => $catalogProductWebsite],
                'main_table.linked_product_id = product_website.product_id',
                []
            )->where('product_website.website_id = ?', $websiteId)
            ->where('main_table.linked_product_id IN (?)', $linkedProductIds);
            $linkedStoreProductIds = $this->connection->fetchAll($select);
        }
        return !empty($linkedStoreProductIds) ?
            array_column($linkedStoreProductIds, 'linked_product_id')
            :[];
    }
}
