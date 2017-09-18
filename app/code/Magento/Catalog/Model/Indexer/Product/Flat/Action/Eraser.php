<?php
/**
 * Flat item ereaser. Used to clear items from flat table
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product\Flat\Action;

use Magento\Framework\App\ResourceConnection;

class Eraser
{
    /**
     * @var \Magento\Catalog\Helper\Product\Flat\Indexer
     */
    protected $productIndexerHelper;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Catalog\Helper\Product\Flat\Indexer $productHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Catalog\Helper\Product\Flat\Indexer $productHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->productIndexerHelper = $productHelper;
        $this->connection = $resource->getConnection();
        $this->storeManager = $storeManager;
    }

    /**
     * Remove products from flat that are not exist
     *
     * @param array $ids
     * @param int $storeId
     * @return void
     */
    public function removeDeletedProducts(array &$ids, $storeId)
    {
        $select = $this->connection->select()->from(
            $this->productIndexerHelper->getTable('catalog_product_entity')
        )->where(
            'entity_id IN(?)',
            $ids
        );
        $result = $this->connection->query($select);

        $existentProducts = [];
        foreach ($result->fetchAll() as $product) {
            $existentProducts[] = $product['entity_id'];
        }

        $productsToDelete = array_diff($ids, $existentProducts);
        $ids = $existentProducts;

        $this->deleteProductsFromStore($productsToDelete, $storeId);
    }

    /**
     * Delete products from flat table(s)
     *
     * @param int|array $productId
     * @param null|int $storeId
     * @return void
     */
    public function deleteProductsFromStore($productId, $storeId = null)
    {
        if (!is_array($productId)) {
            $productId = [$productId];
        }
        if (null === $storeId) {
            foreach ($this->storeManager->getStores() as $store) {
                $this->connection->delete(
                    $this->productIndexerHelper->getFlatTableName($store->getId()),
                    ['entity_id IN(?)' => $productId]
                );
            }
        } else {
            $this->connection->delete(
                $this->productIndexerHelper->getFlatTableName((int)$storeId),
                ['entity_id IN(?)' => $productId]
            );
        }
    }
}
