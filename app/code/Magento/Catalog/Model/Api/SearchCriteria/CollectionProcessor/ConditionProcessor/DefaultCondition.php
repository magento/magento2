<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Api\SearchCriteria\CollectionProcessor\ConditionProcessor;

use Magento\Framework\Api\SearchCriteria\CollectionProcessor\ConditionProcessor\CustomConditionInterface;
use Magento\Framework\Api\Filter;
use Magento\Catalog\Model\Api\SearchCriteria\CollectionProcessor\ConditionProcessor\ConditionBuilder\Factory;

/**
 * Default condition builder for Catalog\Model\ResourceModel\Product\Collection
 */
class DefaultCondition implements CustomConditionInterface
{
    /**
     * @var Factory
     */
    private $conditionBuilderFactory;

    /**
     * @param Factory $conditionBuilderFactory
     */
    public function __construct(
        Factory $conditionBuilderFactory
    ) {
        $this->conditionBuilderFactory = $conditionBuilderFactory;
    }

    /**
     * Builds condition to filter product collection either by EAV or by native attribute
     *
     * @param Filter $filter
     * @return string
     */
    public function build(Filter $filter): string
    {
        $filterBuilder = $this->conditionBuilderFactory->createByFilter($filter);

        return $filterBuilder->build($filter);
    }
}
