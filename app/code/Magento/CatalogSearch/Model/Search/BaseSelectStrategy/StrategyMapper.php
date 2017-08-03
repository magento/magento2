<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Search\BaseSelectStrategy;

use Magento\CatalogSearch\Model\Search\SelectContainer\SelectContainer;
use Magento\CatalogSearch\Model\Adapter\Mysql\BaseSelectStrategy\BaseSelectFullTextSearchStrategy;
use Magento\CatalogSearch\Model\Adapter\Mysql\BaseSelectStrategy\BaseSelectAttributesSearchStrategy;

/**
 * Class StrategyMapper
 * This class is responsible for deciding which BaseSelectStrategyInterface should be used for passed SelectContainer
 * @since 2.2.0
 */
class StrategyMapper
{
    /**
     * @var BaseSelectFullTextSearchStrategy
     * @since 2.2.0
     */
    private $baseSelectFullTextSearchStrategy;

    /**
     * @var BaseSelectAttributesSearchStrategy
     * @since 2.2.0
     */
    private $baseSelectAttributesSearchStrategy;

    /**
     * @param BaseSelectFullTextSearchStrategy $baseSelectFullTextSearchStrategy
     * @param BaseSelectAttributesSearchStrategy $baseSelectAttributesSearchStrategy
     * @since 2.2.0
     */
    public function __construct(
        BaseSelectFullTextSearchStrategy $baseSelectFullTextSearchStrategy,
        BaseSelectAttributesSearchStrategy $baseSelectAttributesSearchStrategy
    ) {
        $this->baseSelectFullTextSearchStrategy = $baseSelectFullTextSearchStrategy;
        $this->baseSelectAttributesSearchStrategy = $baseSelectAttributesSearchStrategy;
    }

    /**
     * Decides which BaseSelectStrategyInterface should be used
     *
     * @param SelectContainer $selectContainer
     * @return BaseSelectStrategyInterface
     * @since 2.2.0
     */
    public function mapSelectContainerToStrategy(SelectContainer $selectContainer)
    {
        if ($selectContainer->isFullTextSearchRequired()
            && !$selectContainer->hasCustomAttributesFilters()
            && !$selectContainer->hasVisibilityFilter()
        ) {
            return $this->baseSelectFullTextSearchStrategy;
        }

        return $this->baseSelectAttributesSearchStrategy;
    }
}
