<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Adapter\Mysql;

use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\Search\Request\Dimension;

class Dimensions
{
    const STORE_FIELD_NAME = 'store_id';

    /**
     * @var ScopeResolverInterface
     */
    private $scopeResolver;

    /**
     * @var ConditionManager
     */
    private $conditionManager;

    /**
     * @param ScopeResolverInterface $scopeResolver
     * @param ConditionManager $conditionManager
     */
    public function __construct(
        ScopeResolverInterface $scopeResolver,
        ConditionManager $conditionManager
    ) {
        $this->scopeResolver = $scopeResolver;
        $this->conditionManager = $conditionManager;
    }

    /**
     * @param Dimension $dimension
     * @return string
     */
    public function build(Dimension $dimension)
    {
        return $this->generateExpression($dimension);
    }

    /**
     * @param Dimension $dimension
     * @return string
     */
    private function generateExpression(Dimension $dimension)
    {
        $field = $dimension->getName();
        $value = $dimension->getValue();

        if ('scope' === $field) {
            $field = self::STORE_FIELD_NAME;
            $value = $this->scopeResolver->getScope($value)->getId();
        }

        return $this->conditionManager->generateCondition('search_index.' . $field, '=', $value);
    }
}
