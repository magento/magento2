<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Category\DataProvider;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;

/**
 * Breadcrumbs data provider
 */
class Breadcrumbs
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Get breadcrumbs data
     *
     * @param string $categoryPath
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getData(string $categoryPath): array
    {
        $breadcrumbsData = [];

        $pathCategoryIds = explode('/', $categoryPath);
        $parentCategoryIds = array_slice($pathCategoryIds, 2, -1);

        if (count($parentCategoryIds)) {
            $collection = $this->collectionFactory->create();
            $collection->addAttributeToSelect(['name', 'url_key', 'url_path']);
            $collection->addAttributeToFilter('entity_id', $parentCategoryIds);
            $collection->addAttributeToFilter(CategoryInterface::KEY_IS_ACTIVE, 1);

            foreach ($collection as $category) {
                $breadcrumbsData[] = [
                    'category_id' => $category->getId(),
                    'category_name' => $category->getName(),
                    'category_level' => $category->getLevel(),
                    'category_url_key' => $category->getUrlKey(),
                    'category_url_path' => $category->getUrlPath(),
                ];
            }
        }
        return $breadcrumbsData;
    }
}
