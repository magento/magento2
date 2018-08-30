<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Category;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\CustomAttributesFlattener;
use Magento\Framework\Reflection\DataObjectProcessor;

/**
 * Hydrate GraphQL category structure with model data.
 */
class Hydrator
{
    /**
     * @var CustomAttributesFlattener
     */
    private $flattener;

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @param CustomAttributesFlattener $flattener
     * @param DataObjectProcessor $dataObjectProcessor
     */
    public function __construct(
        CustomAttributesFlattener $flattener,
        DataObjectProcessor $dataObjectProcessor
    ) {
        $this->flattener = $flattener;
        $this->dataObjectProcessor = $dataObjectProcessor;
    }

    /**
     * Hydrate and flatten category object to flat array
     *
     * @param CategoryInterface $category
     * @return array
     */
    public function hydrateCategory(CategoryInterface $category) : array
    {
        $categoryData = $this->dataObjectProcessor->buildOutputDataArray($category, CategoryInterface::class);
        $categoryData['id'] = $category->getId();
        $categoryData['product_count'] = $category->getProductCount();
        $categoryData['children'] = [];
        $categoryData['available_sort_by'] = $category->getAvailableSortBy();
        return $this->flattener->flatten($categoryData);
    }
}
