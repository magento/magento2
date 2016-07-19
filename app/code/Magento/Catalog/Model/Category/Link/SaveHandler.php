<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Category\Link;

use Magento\Catalog\Api\Data\CategoryLinkInterface;
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
     * @var \Magento\Framework\EntityManager\HydratorPool
     */
    private $hydratorPool;

    /**
     * SaveHandler constructor.
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\CategoryLink $productCategoryLink
     * @param \Magento\Framework\EntityManager\HydratorPool $hydratorPool
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CategoryLink $productCategoryLink,
        \Magento\Framework\EntityManager\HydratorPool $hydratorPool
    ) {
        $this->productCategoryLink = $productCategoryLink;
        $this->hydratorPool = $hydratorPool;
    }

    /**
     * @param object $entity
     * @param array $arguments
     * @return object
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        $extensionAttributes = $entity->getExtensionAttributes();
        if ($extensionAttributes === null) {
            return $entity;
        }

        $categoryLinks = $extensionAttributes->getCategoryLinks();

        if ($categoryLinks !== null) {
            $hydrator = $this->hydratorPool->getHydrator(CategoryLinkInterface::class);
            $categoryLinks = array_map(function ($categoryLink) use ($hydrator) {
                return $hydrator->extract($categoryLink) ;
            }, $categoryLinks);
            $this->productCategoryLink->saveCategoryLinks($entity, $categoryLinks);
        }

        return $entity;
    }
}
