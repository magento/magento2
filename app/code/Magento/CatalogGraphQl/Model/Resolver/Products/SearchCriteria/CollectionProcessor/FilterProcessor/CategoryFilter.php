<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Products\SearchCriteria\CollectionProcessor\FilterProcessor;

use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResourceModel;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor\FilterProcessor\CustomFilterInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;

/**
 * Category filter allows to filter products collection using 'category_id' filter from search criteria.
 */
class CategoryFilter implements CustomFilterInterface
{
    /**
     * @var CategoryFactory
     */
    private $categoryFactory;

    /**
     * @var CategoryResourceModel
     */
    private $categoryResourceModel;

    /**
     * @param CategoryFactory $categoryFactory
     * @param CategoryResourceModel $categoryResourceModel
     */
    public function __construct(
        CategoryFactory $categoryFactory,
        CategoryResourceModel $categoryResourceModel
    ) {
        $this->categoryFactory = $categoryFactory;
        $this->categoryResourceModel = $categoryResourceModel;
    }

    /**
     * Apply filter by 'category_id' to product collection.
     *
     * For anchor categories, the products from all children categories will be present in the result.
     *
     * @param Filter $filter
     * @param AbstractDb $collection
     * @return bool Whether the filter is applied
     * @throws LocalizedException
     */
    public function apply(Filter $filter, AbstractDb $collection)
    {
        $conditionType = $filter->getConditionType();

        if ($conditionType !== 'eq') {
            throw new LocalizedException(__("'category_id' only supports 'eq' condition type."));
        }

        $categoryId = $filter->getValue();
        /** @var Collection $collection */
        $category = $this->categoryFactory->create();
        $this->categoryResourceModel->load($category, $categoryId);
        $collection->addCategoryFilter($category);

        return true;
    }
}
