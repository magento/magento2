<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Category;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Catalog\Model\ResourceModel\Category\Collection;

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
     * @param Collection $categoryCollection
     */
    public function applyFilters(
        array $args,
        Collection $categoryCollection
    ): void {
        $categoryCollection->addAttributeToFilter(CategoryInterface::KEY_IS_ACTIVE, ['eq' => 1]);
        foreach ($args['filters'] as $field => $cond) {
            foreach ($cond as $condType => $value) {
                if ($field === 'ids') {
                    $categoryCollection->addIdFilter($value);
                } else {
                    $this->addAttributeFilter($categoryCollection, $field, $condType, $value);
                }
            }
        }
    }

    /**
     * @param Collection $categoryCollection
     * @param string $field
     * @param string $condType
     * @param string|array $value
     */
    private function addAttributeFilter($categoryCollection, $field, $condType, $value)
    {
        if ($condType === 'match') {
            $this->addMatchFilter($categoryCollection, $field, $value);
            return;
        }
        $categoryCollection->addAttributeToFilter($field, [$condType => $value]);
    }

    /**
     *
     * @param Collection $categoryCollection
     * @param string $field
     * @param string $value
     */
    private function addMatchFilter($categoryCollection, $field, $value)
    {
        $categoryCollection->addAttributeToFilter($field, ['like' => "%{$value}%"]);
    }
}
