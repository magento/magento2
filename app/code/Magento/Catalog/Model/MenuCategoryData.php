<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model;

use Magento\Catalog\Helper\Category as CategoryHelper;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\Registry;

class MenuCategoryData
{
    /**
     * @var Layer
     */
    private $catalogLayer = null;

    /**
     * @param CategoryHelper $catalogCategory
     * @param Resolver $layerResolver
     * @param Registry $registry
     */
    public function __construct(
        private readonly CategoryHelper $catalogCategory,
        private readonly Resolver $layerResolver,
        private readonly Registry $registry,
    ) {
    }

    /**
     * Get category data to be added to the Menu
     *
     * @param Category $category
     * @return array
     */
    public function getMenuCategoryData(Category $category): array
    {
        $nodeId = 'category-node-' . $category->getId();

        $isActiveCategory = false;
        /** @var Category $currentCategory */
        $currentCategory = $this->registry->registry('current_category');
        if ($currentCategory && $currentCategory->getId() == $category->getId()) {
            $isActiveCategory = true;
        }

        $categoryData = [
            'name' => $category->getName(),
            'id' => $nodeId,
            'url' => $this->catalogCategory->getCategoryUrl($category),
            'has_active' => $this->hasActive($category),
            'is_active' => $isActiveCategory,
        ];

        return $categoryData;
    }

    /**
     * Checks whether category belongs to active category's path
     *
     * @param Category $category
     * @return bool
     */
    private function hasActive(Category $category): bool
    {
        $catalogLayer = $this->getCatalogLayer();
        if (!$catalogLayer) {
            return false;
        }

        $currentCategory = $catalogLayer->getCurrentCategory();
        if (!$currentCategory) {
            return false;
        }

        $categoryPathIds = explode(',', $currentCategory->getPathInStore());
        return in_array($category->getId(), $categoryPathIds);
    }

    /**
     * Get catalog layer
     *
     * @return Layer
     */
    private function getCatalogLayer(): Layer
    {
        if ($this->catalogLayer === null) {
            $this->catalogLayer = $this->layerResolver->get();
        }
        return $this->catalogLayer;
    }
}
