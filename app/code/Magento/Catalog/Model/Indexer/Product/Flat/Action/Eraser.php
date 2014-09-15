<?php
/**
 * Flat item ereaser. Used to clear items from flat table
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Model\Indexer\Product\Flat\Action;

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
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Catalog\Helper\Product\Flat\Indexer $productHelper
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Resource $resource,
        \Magento\Catalog\Helper\Product\Flat\Indexer $productHelper,
        \Magento\Framework\StoreManagerInterface $storeManager
    ) {
        $this->productIndexerHelper = $productHelper;
        $this->connection = $resource->getConnection('default');
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

        $existentProducts = array();
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
            $productId = array($productId);
        }
        if (null === $storeId) {
            foreach ($this->storeManager->getStores() as $store) {
                $this->connection->delete(
                    $this->productIndexerHelper->getFlatTableName($store->getId()),
                    array('entity_id IN(?)' => $productId)
                );
            }
        } else {
            $this->connection->delete(
                $this->productIndexerHelper->getFlatTableName((int)$storeId),
                array('entity_id IN(?)' => $productId)
            );
        }
    }
}
