<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Api\SearchCriteria\CollectionProcessor\FilterProcessor;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor\FilterProcessor\CustomFilterInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class ProductCategoryFilter implements CustomFilterInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Apply category_id Filter to Product Collection
     *
     * @param Filter $filter
     * @param AbstractDb $collection
     * @return bool Whether the filter is applied
     */
    public function apply(Filter $filter, AbstractDb $collection)
    {
        $value = $filter->getValue();
        $conditionType = $filter->getConditionType() ?: 'in';
        $categoryIds = explode(',', $value);
        $categoryFilter = [$conditionType => $categoryIds];

        /** @var Collection $collection */
        $collection->addCategoriesFilter($categoryFilter);
        if (count($categoryIds) === 1) {
            try {
                $collection->joinField(
                    'position',
                    'catalog_category_product',
                    'position',
                    'product_id=entity_id',
                    ['category_id' => $value],
                    'left'
                );
            } catch (LocalizedException $e) {
                $this->logger->warning($e->getMessage());
            }
        }

        return true;
    }
}
