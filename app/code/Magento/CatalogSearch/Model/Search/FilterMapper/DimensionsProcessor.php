<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Search\FilterMapper;

use Magento\Framework\Search\Adapter\Mysql\ConditionManager;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\RequestInterface;
use Magento\Framework\Search\Request\Dimension;
use Magento\CatalogSearch\Model\Search\SelectContainer\SelectContainer;

/**
 * Class DimensionsProcessor
 * Adds dimension conditions to select query
 * @since 2.2.0
 */
class DimensionsProcessor
{
    /**
     * @var ConditionManager
     * @since 2.2.0
     */
    private $conditionManager;

    /**
     * @var ScopeResolverInterface
     * @since 2.2.0
     */
    private $dimensionScopeResolver;

    /**
     * @param ConditionManager $conditionManager
     * @param ScopeResolverInterface $dimensionScopeResolver
     * @since 2.2.0
     */
    public function __construct(
        ConditionManager $conditionManager,
        ScopeResolverInterface $dimensionScopeResolver
    ) {
        $this->conditionManager = $conditionManager;
        $this->dimensionScopeResolver = $dimensionScopeResolver;
    }

    /**
     * Adds dimension conditions to select query
     *
     * @param SelectContainer $selectContainer
     * @return SelectContainer
     * @since 2.2.0
     */
    public function processDimensions(SelectContainer $selectContainer)
    {
        $query = $this->conditionManager->combineQueries(
            $this->prepareDimensions($selectContainer->getDimensions()),
            Select::SQL_OR
        );

        if (!empty($query)) {
            $select = $selectContainer->getSelect();
            $select->where($this->conditionManager->wrapBrackets($query));
            $selectContainer = $selectContainer->updateSelect($select);
        }

        return $selectContainer;
    }

    /**
     * Prepares where conditions from dimensions
     *
     * @param Dimension[] $dimensions
     * @return string[]
     * @since 2.2.0
     */
    private function prepareDimensions(array $dimensions)
    {
        $preparedDimensions = [];

        foreach ($dimensions as $dimension) {
            if ('scope' === $dimension->getName()) {
                continue;
            }
            $preparedDimensions[] = $this->conditionManager->generateCondition(
                $dimension->getName(),
                '=',
                $this->dimensionScopeResolver->getScope($dimension->getValue())->getId()
            );
        }

        return $preparedDimensions;
    }
}
