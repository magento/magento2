<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Category;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;

/**
 * Category filter allows to filter collection using 'id, url_key, name' from search criteria.
 */
class CategoryFilter
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
     * Filter for filtering the requested categories id's based on url_key, ids, name in the result.
     *
     * @param array $args
     * @return array
     */
    public function applyFilters(array $args): array
    {
        $categoryCollection = $this->collectionFactory->create();
        foreach ($args['filters'] as $field => $cond) {
            foreach ($cond as $condType => $value) {
                if ($field === 'ids') {
                    $categoryCollection->addIdFilter($value);
                } else {
                    $categoryCollection->addAttributeToFilter($field, [$condType => $value]);
                }
            }
        }
        $categoryIds = [];
        foreach ($categoryCollection as $category) {
            $categoryIds[] = (int)$category->getId();
        }
        return $categoryIds;
    }
}
