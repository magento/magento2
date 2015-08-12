<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Adapter\Mysql\Filter\Builder;

use Magento\Framework\Search\Adapter\Mysql\ConditionManager;

class Wildcard implements FilterInterface
{
    const CONDITION_LIKE = 'LIKE';
    const CONDITION_NOT_LIKE = 'NOT LIKE';

    /**
     * @var ConditionManager
     */
    private $conditionManager;

    /**
     * @param ConditionManager $conditionManager
     */
    public function __construct(
        ConditionManager $conditionManager
    ) {
        $this->conditionManager = $conditionManager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildFilter(
        \Magento\Framework\Search\Request\FilterInterface $filter,
        $isNegation
    ) {
        /** @var \Magento\Framework\Search\Request\Filter\Wildcard $filter */

        $searchValue = '%' . $filter->getValue() . '%';
        return $this->conditionManager->generateCondition(
            $filter->getField(),
            ($isNegation ? self::CONDITION_NOT_LIKE : self::CONDITION_LIKE),
            $searchValue
        );
    }
}
