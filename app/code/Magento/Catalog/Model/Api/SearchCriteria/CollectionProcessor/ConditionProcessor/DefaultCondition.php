<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Api\SearchCriteria\CollectionProcessor\ConditionProcessor;

use Magento\Framework\Api\SearchCriteria\CollectionProcessor\ConditionProcessor\CustomConditionInterface;
use Magento\Framework\Api\Filter;
use Magento\Catalog\Model\Api\SearchCriteria\CollectionProcessor\ConditionProcessor\ConditionBuilder\ConditionBuilderFactory;

/**
 * Class DefaultCondition
 * Default condition builder for Catalog\Model\ResourceModel\Product\Collection
 *
 * @package Magento\Catalog\Model\Api\SearchCriteria\CollectionProcessor\ConditionProcessor
 */
class DefaultCondition implements CustomConditionInterface
{
    /**
     * @var ConditionBuilderFactory
     */
    private $conditionBuilderFactory;

    /**
     * @param ConditionBuilderFactory $conditionBuilderFactory
     */
    public function __construct(
        ConditionBuilderFactory $conditionBuilderFactory
    ) {
        $this->conditionBuilderFactory = $conditionBuilderFactory;
    }

    /**
     * @param Filter $filter
     * @return string
     */
    public function build(Filter $filter)
    {
        $filterBuilder = $this->conditionBuilderFactory->createByFilter($filter);

        return $filterBuilder->build($filter);
    }
}
