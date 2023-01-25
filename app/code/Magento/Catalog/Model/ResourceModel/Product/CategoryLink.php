<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel\Product;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\CategoryLinkInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\ResourceConnection;

/**
 * Product CategoryLink resource model
 */
class CategoryLink
{
    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    private $metadataPool;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var \Magento\Framework\EntityManager\EntityMetadataInterface
     */
    private $categoryLinkMetadata;

    /**
     * CategoryLink constructor.
     *
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        \Magento\Framework\EntityManager\MetadataPool $metadataPool,
        ResourceConnection $resourceConnection
    ) {
        $this->metadataPool = $metadataPool;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Retrieve product category links by ProductInterface and category identifiers
     *
     * @param ProductInterface $product
     * @param array $categoryIds
     * @return array
     */
    public function getCategoryLinks(ProductInterface $product, array $categoryIds = [])
    {
        $connection = $this->resourceConnection->getConnection();

        $select = $connection->select();
        $select->from($this->getCategoryLinkMetadata()->getEntityTable(), ['category_id', 'position']);
        $select->where('product_id = ?', (int)$product->getId());

        if (!empty($categoryIds)) {
            $select->where('category_id IN(?)', $categoryIds);
        }

        $result = $connection->fetchAll($select);

        return $result;
    }

    /**
     * Save product category links and return affected category identifiers
     *
     * @param ProductInterface $product
     * @param array $categoryLinks
     * @return array
     */
    public function saveCategoryLinks(ProductInterface $product, array $categoryLinks = [])
    {
        $categoryLinks = $this->verifyCategoryLinks($categoryLinks);
        $oldCategoryLinks = $this->getCategoryLinks($product);

        $insertUpdate = $this->processCategoryLinks($categoryLinks, $oldCategoryLinks);
        $deleteUpdate = $this->processCategoryLinks($oldCategoryLinks, $categoryLinks);

        list($delete, $insert, $update) = $this->analyseUpdatedLinks($deleteUpdate, $insertUpdate);

        return array_merge(
            $this->deleteCategoryLinks($product, $delete),
            $this->updateCategoryLinks($product, $insert, true),
            $this->updateCategoryLinks($product, $update)
        );
    }

    /**
     * Get category link metadata
     *
     * @return \Magento\Framework\EntityManager\EntityMetadataInterface
     */
    private function getCategoryLinkMetadata()
    {
        if ($this->categoryLinkMetadata == null) {
            $this->categoryLinkMetadata = $this->metadataPool->getMetadata(CategoryLinkInterface::class);
        }

        return $this->categoryLinkMetadata;
    }

    /**
     * Process category links
     *
     * @param array $newCategoryPositions
     * @param array $oldCategoryPositions
     * @return array
     */
    private function processCategoryLinks($newCategoryPositions, &$oldCategoryPositions)
    {
        $result = ['changed' => [], 'updated' => []];

        $oldCategoryPositions = array_values($oldCategoryPositions);
        foreach ($newCategoryPositions as $newCategoryPosition) {
            $key = false;

            foreach ($oldCategoryPositions as $oldKey => $oldCategoryPosition) {
                if ((int)$oldCategoryPosition['category_id'] === (int)$newCategoryPosition['category_id']) {
                    $key = $oldKey;
                    break;
                }
            }

            if ($key === false) {
                $result['changed'][] = $newCategoryPosition;
            } elseif ($oldCategoryPositions[$key]['position'] != $newCategoryPosition['position']) {
                $result['updated'][] = $newCategoryPosition;
                unset($oldCategoryPositions[$key]);
            }
        }

        return $result;
    }

    /**
     * Update category links
     *
     * @param ProductInterface $product
     * @param array $insertLinks
     * @param bool $insert
     * @return array
     */
    public function updateCategoryLinks(ProductInterface $product, array $insertLinks, $insert = false)
    {
        if (empty($insertLinks)) {
            return [];
        }

        $data = [];
        foreach ($insertLinks as $categoryLink) {
            $data[] = [
                'category_id' => (int)$categoryLink['category_id'],
                'product_id' => (int)$product->getId(),
                'position' => $categoryLink['position'],
            ];
        }

        if ($data) {
            $connection = $this->resourceConnection->getConnection();
            if ($insert) {
                $connection->insertArray(
                    $this->getCategoryLinkMetadata()->getEntityTable(),
                    array_keys($data[0]),
                    $data,
                    \Magento\Framework\DB\Adapter\AdapterInterface::INSERT_IGNORE
                );
            } else {
                // for mass update category links with constraint by unique key use insert on duplicate statement
                $connection->insertOnDuplicate(
                    $this->getCategoryLinkMetadata()->getEntityTable(),
                    $data,
                    ['position']
                );
            }
        }

        return array_column($insertLinks, 'category_id');
    }

    /**
     * Delete category links
     *
     * @param ProductInterface $product
     * @param array $deleteLinks
     * @return array
     */
    private function deleteCategoryLinks(ProductInterface $product, array $deleteLinks)
    {
        if (empty($deleteLinks)) {
            return [];
        }

        $connection = $this->resourceConnection->getConnection();
        $connection->delete($this->getCategoryLinkMetadata()->getEntityTable(), [
            'product_id = ?' => (int)$product->getId(),
            'category_id IN(?)' => array_column($deleteLinks, 'category_id')
        ]);

        return array_column($deleteLinks, 'category_id');
    }

    /**
     * Verify category links identifiers and return valid links
     *
     * @param array $links
     * @return array
     */
    private function verifyCategoryLinks(array $links)
    {
        if (empty($links)) {
            return [];
        }

        $categoryMetadata = $this->metadataPool->getMetadata(CategoryInterface::class);

        $connection = $this->resourceConnection->getConnection();

        $select = $connection->select();
        $select->from($categoryMetadata->getEntityTable(), 'entity_id');
        $select->where('entity_id IN(?)', array_column($links, 'category_id'));

        $result = $connection->fetchCol($select);
        $validLinks = array_map(function ($categoryId) use ($links) {
            $key = array_search($categoryId, array_column($links, 'category_id'));
            if ($key !== false) {
                return $links[$key];
            }
        }, $result);

        return $validLinks;
    }

    /**
     * Analyse category links for update or/and delete. Return array of links for delete, insert and update
     *
     * @param array $deleteUpdate
     * @param array $insertUpdate
     * @return array
     */
    private function analyseUpdatedLinks($deleteUpdate, $insertUpdate)
    {
        $delete = $deleteUpdate['changed'] ?: [];
        $insert = $insertUpdate['changed'] ?: [];
        $insert = array_merge_recursive($insert, $deleteUpdate['updated']);

        return [$delete, $insert, $insertUpdate['updated']];
    }
}
