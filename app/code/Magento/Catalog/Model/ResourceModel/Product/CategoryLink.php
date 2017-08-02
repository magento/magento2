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
 * @since 2.2.0
 */
class CategoryLink
{
    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     * @since 2.2.0
     */
    private $metadataPool;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     * @since 2.2.0
     */
    private $resourceConnection;

    /**
     * @var \Magento\Framework\EntityManager\EntityMetadataInterface
     * @since 2.2.0
     */
    private $categoryLinkMetadata;

    /**
     * CategoryLink constructor.
     *
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
     * @param ResourceConnection $resourceConnection
     * @since 2.2.0
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
     * @since 2.2.0
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
     * @since 2.2.0
     */
    public function saveCategoryLinks(ProductInterface $product, array $categoryLinks = [])
    {
        $categoryLinks = $this->verifyCategoryLinks($categoryLinks);
        $oldCategoryLinks = $this->getCategoryLinks($product);

        $insertUpdate = $this->processCategoryLinks($categoryLinks, $oldCategoryLinks);
        $deleteUpdate = $this->processCategoryLinks($oldCategoryLinks, $categoryLinks);

        list($delete, $insert) = $this->analyseUpdatedLinks($deleteUpdate, $insertUpdate);

        return array_merge(
            $this->updateCategoryLinks($product, $insert),
            $this->deleteCategoryLinks($product, $delete)
        );
    }

    /**
     * @return \Magento\Framework\EntityManager\EntityMetadataInterface
     * @since 2.2.0
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
     * @since 2.2.0
     */
    private function processCategoryLinks($newCategoryPositions, &$oldCategoryPositions)
    {
        $result = ['changed' => [], 'updated' => []];
        foreach ($newCategoryPositions as $newCategoryPosition) {
            $key = array_search(
                $newCategoryPosition['category_id'],
                array_column($oldCategoryPositions, 'category_id')
            );

            if ($key === false) {
                $result['changed'][] = $newCategoryPosition;
            } elseif ($oldCategoryPositions[$key]['position'] != $newCategoryPosition['position']) {
                $result['updated'][] = $newCategoryPositions[$key];
                unset($oldCategoryPositions[$key]);
            }
        }

        return $result;
    }

    /**
     * @param ProductInterface $product
     * @param array $insertLinks
     * @return array
     * @since 2.2.0
     */
    private function updateCategoryLinks(ProductInterface $product, array $insertLinks)
    {
        if (empty($insertLinks)) {
            return [];
        }

        $connection = $this->resourceConnection->getConnection();

        $data = [];
        foreach ($insertLinks as $categoryLink) {
            $data[] = [
                'category_id' => (int)$categoryLink['category_id'],
                'product_id' => (int)$product->getId(),
                'position' => $categoryLink['position'],
            ];
        }

        if ($data) {
            $connection->insertOnDuplicate(
                $this->getCategoryLinkMetadata()->getEntityTable(),
                $data,
                ['position']
            );
        }

        return array_column($insertLinks, 'category_id');
    }

    /**
     * @param ProductInterface $product
     * @param array $deleteLinks
     * @return array
     * @since 2.2.0
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
     * @since 2.2.0
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
     * Analyse category links for update or/and delete
     *
     * @param array $deleteUpdate
     * @param array $insertUpdate
     * @return array
     * @since 2.2.0
     */
    private function analyseUpdatedLinks($deleteUpdate, $insertUpdate)
    {
        $delete = $deleteUpdate['changed'] ?: [];
        $insert = $insertUpdate['changed'] ?: [];
        $insert = array_merge_recursive($insert, $deleteUpdate['updated']);
        $insert = array_merge_recursive($insert, $insertUpdate['updated']);

        return [$delete, $insert];
    }
}
