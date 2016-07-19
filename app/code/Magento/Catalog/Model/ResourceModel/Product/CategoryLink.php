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
        return $connection->fetchAll($select);
    }

    /**
     * @param ProductInterface $product
     * @param array $categoryIds
     */
    public function saveCategoryLinks(ProductInterface $product, array $categoryIds = [])
    {
        $categoryPositions = array_map(function ($categoryData) {
            if (is_array($categoryData)) {
                return $categoryData;
            } else {
                return ['category_id' => (int)$categoryData, 'position' => 1];
            }
        }, $categoryIds);
        $oldCategoryPositions = $this->getCategoryLinks($product);


        $insert = [];
        $delete = [];
        foreach ($oldCategoryPositions as $oldCategoryPosition)
        {
            $key = array_search($oldCategoryPosition['category_id'], array_column($categoryPositions, 'category_id'));
            if ($key === false) {
                $delete[] = $oldCategoryPosition['category_id'];
            } else {
                if ($categoryPositions[$key]['position'] == $oldCategoryPosition['position']) {
                    continue;
                } else {
                    $insert[] = $categoryPositions[$key];
                    unset($categoryPositions[$key]);
                }
            }
        }

        foreach ($categoryPositions as $categoryPosition)
        {
            $key = array_search($categoryPosition['category_id'], array_column($oldCategoryPositions, 'category_id'));
            if ($key === false) {
                $insert[] = $categoryPosition;
            } else {
                if ($oldCategoryPositions[$key]['position'] == $categoryPosition['position']) {
                    continue;
                } else {
                    $insert[] = $categoryPositions[$key];
                }
            }
        }

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
}
