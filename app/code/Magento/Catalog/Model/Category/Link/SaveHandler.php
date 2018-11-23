<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Category\Link;

use Magento\Catalog\Api\Data\CategoryLinkInterface;
use Magento\Catalog\Model\Indexer\Product\Category;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Save handler for catalog product link.
 */
class SaveHandler implements ExtensionInterface
{

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CategoryLink
     */
    private $productCategoryLink;
    
    /**
     * @var \Magento\Catalog\Helper\Data
     */
    private $helper;
    
    /**
     * @var \Magento\Framework\EntityManager\HydratorPool
     */
    private $hydratorPool;

    /**
     * SaveHandler constructor.
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\CategoryLink $productCategoryLink
     * @param \Magento\Framework\EntityManager\HydratorPool $hydratorPool
     * @param \Magento\Catalog\Helper\Data $helper
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CategoryLink $productCategoryLink,
        \Magento\Framework\EntityManager\HydratorPool $hydratorPool,
        \Magento\Catalog\Helper\Data $helper = null
    ) {
        $this->productCategoryLink = $productCategoryLink;
        $this->hydratorPool = $hydratorPool;
        $this->helper = $helper;
    }

    /**
     * @param object $entity
     * @param array $arguments
     * @return object
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        $entity->setIsChangedCategories(false);

        $extensionAttributes = $entity->getExtensionAttributes();
        if ($extensionAttributes === null && !$entity->hasCategoryIds()) {
            return $entity;
        }

        $modelCategoryLinks = $this->getCategoryLinksPositions($entity);

        $dtoCategoryLinks = $extensionAttributes->getCategoryLinks();
        if ($dtoCategoryLinks !== null) {
            $hydrator = $this->hydratorPool->getHydrator(CategoryLinkInterface::class);
            $dtoCategoryLinks = array_map(function ($categoryLink) use ($hydrator) {
                return $hydrator->extract($categoryLink);
            }, $dtoCategoryLinks);
            $processLinks = $this->mergeCategoryLinks($dtoCategoryLinks, $modelCategoryLinks);
        } else {
            $processLinks = $modelCategoryLinks;
        }

        $affectedCategoryIds = $this->productCategoryLink->saveCategoryLinks($entity, $processLinks);

        if (!empty($affectedCategoryIds)) {
            $entity->setAffectedCategoryIds($affectedCategoryIds);
            $entity->setIsChangedCategories(true);
        }

        return $entity;
    }

    /**
     * @param object $entity
     * @return array
     */
    private function getCategoryLinksPositions($entity)
    {
        $result = [];
        $currentCategoryLinks = $this->productCategoryLink->getCategoryLinks($entity, $entity->getCategoryIds());
        
        $productPosition = $this->getDataHelper()->getDefaultProductPosition();
        foreach ($entity->getCategoryIds() as $categoryId) {
            $key = array_search($categoryId, array_column($currentCategoryLinks, 'category_id'));
            if ($key === false) {
                $result[] = ['category_id' => (int)$categoryId, 'position' => $productPosition];
            } else {
                $result[] = $currentCategoryLinks[$key];
            }
        }

        return $result;
    }
    
    /**
     * @return \Magento\Catalog\Helper\Data
     */
    private function getDataHelper()
    {
        if (null === $this->helper) {
            $this->helper = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Catalog\Helper\Data::class);
        }
        
        return $this->helper;
    }

    /**
     * Merge category links
     *
     * @param array $newCategoryPositions
     * @param array $oldCategoryPositions
     * @return array
     */
    private function mergeCategoryLinks($newCategoryPositions, $oldCategoryPositions)
    {
        if (empty($newCategoryPositions)) {
            return [];
        }

        $categoryPositions = array_combine(array_column($oldCategoryPositions, 'category_id'), $oldCategoryPositions);
        foreach ($newCategoryPositions as $newCategoryPosition) {
            $categoryId = $newCategoryPosition['category_id'];
            if (!isset($categoryPositions[$categoryId])) {
                $categoryPositions[$categoryId] = ['category_id' => $categoryId];
            }
            $categoryPositions[$categoryId]['position'] = $newCategoryPosition['position'];
        }
        $result = array_values($categoryPositions);

        return $result;
    }
}
