<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Api\SearchCriteria\CollectionProcessor\ConditionProcessor;

use Magento\Framework\Api\Filter;

/**
 * Implement it to build SQL conditions from Magento\Framework\Api\Filter
 *
 * Multiple conditions can be combined into groups with AND or OR combination
 * and applied to select queries as WHERE parts to filter entity collections
 *
 * For example:
 *      Select *
 *      FROM `catalog_product_entity`
 *      WHERE
 *          CustomCondition_1
 *          AND
 *          (CustomCondition_2 OR CustomCondition_3)
 *
 * @api
 */
interface CustomConditionInterface
{
    /**
     * @param Filter $filter
     * @return string
     */
    public function build(Filter $filter): string;
}
