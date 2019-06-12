<?php
/**
 * Flat item ereaser. Used to clear items from flat table
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product\Flat\Action;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Store\Model\Store;

/**
 * Flat item eraser. Used to clear items from the catalog flat table.
 */
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
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    private $metadataPool;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Catalog\Helper\Product\Flat\Indexer $productHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\EntityManager\MetadataPool|null $metadataPool
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Catalog\Helper\Product\Flat\Indexer $productHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool = null
    ) {
        $this->productIndexerHelper = $productHelper;
        $this->connection = $resource->getConnection();
        $this->storeManager = $storeManager;
        $this->metadataPool = $metadataPool ?:
            \Magento\Framework\App\ObjectManager::getInstance()->get(MetadataPool::class);
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
        $select = $this->getSelectForProducts($ids);
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
     * Remove products with "Disabled" status from the flat table(s).
     *
     * @param array $ids
     * @param int $storeId
     * @return void
     */
    public function removeDisabledProducts(array &$ids, $storeId)
    {
        /* @var $statusAttribute \Magento\Eav\Model\Entity\Attribute */
        $statusAttribute = $this->productIndexerHelper->getAttribute('status');
        $productEntityAlias = $this->getProductEntityTableAlias();
        $metadata = $this->metadataPool->getMetadata(ProductInterface::class);
        $linkField = $metadata->getLinkField();

        $select = $this->getSelectForProducts($ids);
        $select->joinLeft(
            ['status_global_attr' => $statusAttribute->getBackendTable()],
            ' status_global_attr.attribute_id = ' . (int)$statusAttribute->getAttributeId()
            . ' AND status_global_attr.'. $linkField .' = '. $productEntityAlias .'.'. $linkField
            . ' AND status_global_attr.store_id = ' . Store::DEFAULT_STORE_ID,
            []
        );
        $select->joinLeft(
            ['status_attr' => $statusAttribute->getBackendTable()],
            ' status_attr.attribute_id = ' . (int)$statusAttribute->getAttributeId()
            . ' AND status_attr.'. $linkField .' = '. $productEntityAlias .'.'. $linkField
            . ' AND status_attr.store_id = ' . $storeId,
            []
        );
        $select->where('IFNULL(status_attr.value, status_global_attr.value) = ?', Status::STATUS_DISABLED);

        $result = $this->connection->query($select);

        $disabledProducts = [];
        foreach ($result->fetchAll() as $product) {
            $disabledProducts[] = $product['entity_id'];
        }

        if (!empty($disabledProducts)) {
            $ids = array_diff($ids, $disabledProducts);
            $this->deleteProductsFromStore($disabledProducts, $storeId);
        }
    }

    /**
     * Get Select object for existed products.
     *
     * @param array $ids
     * @return \Magento\Framework\DB\Select
     */
    private function getSelectForProducts(array $ids)
    {
        $productTable = $this->productIndexerHelper->getTable('catalog_product_entity');
        $productTableAlias = $this->getProductEntityTableAlias();
        $select = $this->connection->select()
            ->from([$productTableAlias => $productTable])
            ->columns('entity_id')
            ->where($productTableAlias . '.entity_id IN(?)', $ids);
        return $select;
    }

    /**
     * Get product table alias.
     *
     * @return string
     */
    private function getProductEntityTableAlias()
    {
        return 'product_table';
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
