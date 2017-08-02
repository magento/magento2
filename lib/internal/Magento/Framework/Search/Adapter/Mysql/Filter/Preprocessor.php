<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Adapter\Mysql\Filter;

use Magento\Framework\Search\Adapter\Mysql\ConditionManager;
use Magento\Framework\Search\Request\FilterInterface;

/**
 * Class \Magento\Framework\Search\Adapter\Mysql\Filter\Preprocessor
 *
 * @since 2.0.0
 */
class Preprocessor implements PreprocessorInterface
{
    /**
     * @var ConditionManager
     * @since 2.0.0
     */
    private $conditionManager;

    /**
     * @param ConditionManager $conditionManager
     * @since 2.0.0
     */
    public function __construct(ConditionManager $conditionManager)
    {
        $this->conditionManager = $conditionManager;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function process(FilterInterface $filter, $isNegation, $query)
    {
        return $query;
    }
}
