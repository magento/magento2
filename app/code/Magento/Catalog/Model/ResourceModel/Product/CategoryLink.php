<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel\Product;

use Magento\Catalog\Api\Data\CategoryLinkInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\ResourceConnection;

/**
 * Product CategoryLink resource model
 */
class CategoryLink
{
    /** @var  \Magento\Framework\EntityManager\MetadataPool */
    private $metadataPool;

    /** @var  ResourceConnection */
    private $resourceConnection;

    /** @var \Magento\Framework\EntityManager\EntityMetadataInterface */
    private $categoryLinkMetadata;

    /**
     * CategoryLink constructor.
     *
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
     * @param ResourceConnection $resourceConnection
     */
    public function __construct
    (
        \Magento\Framework\EntityManager\MetadataPool $metadataPool,
        ResourceConnection $resourceConnection
    ) {
        $this->metadataPool = $metadataPool;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param ProductInterface $product
     * @return array
     */
    public function getCategoryLinks(ProductInterface $product)
    {
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select();
        $select->from($this->getCategoryLinkMetadata()->getEntityTable(), ['category_id', 'position']);
        $select->where('product_id = ?', (int)$product->getId());
        $result = $connection->fetchAll($select);
        return $result;
    }

    /**
     * Save product category links and return affected category_ids
     *
     * @param ProductInterface $product
     * @param array $categoryIds
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function saveCategoryLinks(ProductInterface $product, array $categoryIds = [])
    {
        $newCategoryPositions = array_map(function ($categoryData) {
            if (is_array($categoryData)) {
                return $categoryData;
            } else {
                return ['category_id' => (int)$categoryData, 'position' => 0];
            }
        }, $categoryIds);

        $oldCategoryPositions = $this->getCategoryLinks($product);

        $insertUpdate = $this->processCategoryLinks($newCategoryPositions, $oldCategoryPositions);
        $deleteUpdate = $this->processCategoryLinks($oldCategoryPositions, $newCategoryPositions);

        $delete = isset($deleteUpdate['changed']) ? $deleteUpdate['changed'] : [];
        $insert = isset($insertUpdate['changed']) ? $insertUpdate['changed'] : [];
        $insert = isset($deleteUpdate['updated']) ? array_merge_recursive($insert, $deleteUpdate['updated']) : $insert;
        $insert = isset($insertUpdate['updated']) ? array_merge_recursive($insert, $insertUpdate['updated']) : $insert;

        $connection = $this->resourceConnection->getConnection();
        if (!empty($insert)) {
            $data = [];
            foreach ($insert as $categoryLink) {
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
        }

        if (!empty($delete)) {
            foreach ($delete as $categoryId) {
                $where = ['product_id = ?' => (int)$product->getId(), 'category_id = ?' => (int)$categoryId];
                $connection->delete($this->getCategoryLinkMetadata()->getEntityTable(), $where);
            }
        }
        $result =  array_map(function ($value) {
            return isset($value['category_id']) ? $value['category_id'] : null;
        }, array_merge($insert, $delete));

        return $result;
    }

    /**
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
        $result = [];
        foreach ($newCategoryPositions as $newCategoryPosition) {
            $key = array_search(
                $newCategoryPosition['category_id'],
                array_column($oldCategoryPositions, 'category_id')
            );
            if ($key === false) {
                $result['changed'][] = $newCategoryPosition;
            } else {
                if ($oldCategoryPositions[$key]['position'] == $newCategoryPosition['position']) {
                    continue;
                } else {
                    $result['updated'][] = $newCategoryPositions[$key];
                    unset($oldCategoryPositions[$key]);
                }
            }
        }

        return $result;
    }
}
