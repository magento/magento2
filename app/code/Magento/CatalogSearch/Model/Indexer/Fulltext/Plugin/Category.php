<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */

namespace Magento\CatalogSearch\Model\Indexer\Fulltext\Plugin;

use Magento\Catalog\Model\ResourceModel\Category as ResourceCategory;
use Magento\Framework\Model\AbstractModel;

/**
 * Catalog search indexer plugin for catalog category.
 */
class Category extends AbstractPlugin
{
    /**
     * Reindex on product save
     *
     * @param ResourceCategory $resourceCategory
     * @param \Closure $proceed
     * @param AbstractModel $category
     * @return ResourceCategory
     * @throws \Exception
     */
    public function aroundSave(ResourceCategory $resourceCategory, \Closure $proceed, AbstractModel $category)
    {
        return $this->addCommitCallback($resourceCategory, $proceed, $category);
    }

    /**
     * Reindex catalog search.
     *
     * @param ResourceCategory $resourceCategory
     * @param \Closure $proceed
     * @param AbstractModel $category
     * @return ResourceCategory
     * @throws \Exception
     */
    private function addCommitCallback(ResourceCategory $resourceCategory, \Closure $proceed, AbstractModel $category)
    {
        try {
            $resourceCategory->beginTransaction();
            $isVisibilityChanged = $category->dataHasChangedFor('is_anchor')
                || $category->dataHasChangedFor('is_active');
            $result = $proceed($category);
            $resourceCategory->addCommitCallback(
                function () use ($resourceCategory, $category, $isVisibilityChanged) {
                    $productIds = $category->getAffectedProductIds() ?? [];
                    if ($isVisibilityChanged) {
                        $productIds = array_merge(
                            $productIds,
                            $this->getCategoryTreeProductIds($resourceCategory, $category)
                        );
                    }
                    $productIds = array_values(array_unique(array_map('intval', $productIds)));

                    if (!empty($productIds)) {
                        $this->reindexList($productIds);
                    }
                }
            );
            $resourceCategory->commit();
        } catch (\Exception $e) {
            $resourceCategory->rollBack();
            throw $e;
        }

        return $result;
    }

    /**
     * Get IDs of products assigned to the category or to any of its children
     *
     * A change of is_active or is_anchor can change the category membership of exactly these products:
     * products of child categories are listed in an anchor parent.
     *
     * @param ResourceCategory $resourceCategory
     * @param AbstractModel $category
     * @return array
     */
    private function getCategoryTreeProductIds(ResourceCategory $resourceCategory, AbstractModel $category): array
    {
        $connection = $resourceCategory->getConnection();
        $select = $connection->select()
            ->distinct()
            ->from($resourceCategory->getCategoryProductTable(), ['product_id'])
            ->where('category_id IN (?)', $resourceCategory->getAllChildren($category), \Zend_Db::INT_TYPE);

        return $connection->fetchCol($select);
    }
}
