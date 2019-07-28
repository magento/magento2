<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel\Product\Website;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;

class Link
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resourceConnection;

    /**
     * Link constructor.
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Retrieve associated with product websites ids
     * @param int $productId
     * @return array
     */
    public function getWebsiteIdsByProductId($productId)
    {
        $connection = $this->resourceConnection->getConnection();

        $select = $connection->select()->from(
            $this->getProductWebsiteTable(),
            'website_id'
        )->where(
            'product_id = ?',
            (int) $productId
        );

        return $connection->fetchCol($select);
    }

    /**
     * Return true - if websites was changed, and false - if not
     * @param ProductInterface $product
     * @param array $websiteIds
     * @return bool
     */
    public function saveWebsiteIds(ProductInterface $product, array $websiteIds)
    {
        $connection = $this->resourceConnection->getConnection();

        $oldWebsiteIds = $this->getWebsiteIdsByProductId($product->getId());
        $insert = array_diff($websiteIds, $oldWebsiteIds);
        $delete = array_diff($oldWebsiteIds, $websiteIds);

        if (!empty($insert)) {
            $data = [];
            foreach ($insert as $websiteId) {
                $data[] = ['product_id' => (int) $product->getId(), 'website_id' => (int) $websiteId];
            }
            $connection->insertMultiple($this->getProductWebsiteTable(), $data);
        }

        if (!empty($delete)) {
            foreach ($delete as $websiteId) {
                $condition = ['product_id = ?' => (int) $product->getId(), 'website_id = ?' => (int) $websiteId];
                $connection->delete($this->getProductWebsiteTable(), $condition);
            }
        }

        if (!empty($insert) || !empty($delete)) {
            return true;
        }

        return false;
    }

    /**
     * @return string
     */
    private function getProductWebsiteTable()
    {
        return $this->resourceConnection->getTableName('catalog_product_website');
    }
}
