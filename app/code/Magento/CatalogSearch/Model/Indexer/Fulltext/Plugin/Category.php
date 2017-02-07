<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Indexer\Fulltext\Plugin;

use Magento\Catalog\Model\ResourceModel\Category as ResourceCategory;
use Magento\Framework\Model\AbstractModel;

/**
 * Class Category
 * @package Magento\CatalogSearch\Model\Indexer\Fulltext\Plugin
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
            $result = $proceed($category);
            $resourceCategory->addCommitCallback(function () use ($category) {
                $affectedProducts = $category->getAffectedProductIds();
                if (is_array($affectedProducts)) {
                    $this->reindexList($affectedProducts);
                }
            });
            $resourceCategory->commit();
        } catch (\Exception $e) {
            $resourceCategory->rollBack();
            throw $e;
        }

        return $result;
    }
}
